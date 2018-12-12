<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\AuthorizeRequest;
use App\Api\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use League\Flysystem\Exception;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthController extends ApiController
{

    use ThrottlesLogins;

    protected $username = 'email';

    /**
     * The number of seconds to delay further login attempts.
     * @var integer
     */
    protected $lockoutTime = 60;

    /**
     * Get the maximum number of login attempts for delaying further attempts.
     * @var integer
     */
    protected $maxLoginAttempts = 2;

    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => ['currentUser']]);

        $this->lockoutTime = config('api.login.lockout_time', $this->lockoutTime);

        $this->maxLoginAttempts = config('api.login.max_login_attemps', $this->maxLoginAttempts);

        parent::__construct();
    }

    public function authenticate(AuthorizeRequest $request, User $user)
    {
        // grab credentials from the request

        $credentials = $user->rewriteCredentials($request->only($this->loginUsername(), 'password'));

        $throttling = config('api.login.throttling');

        if ($throttling && $this->hasTooManyLoginAttempts($request)) {

            $seconds = app(RateLimiter::class)->availableIn(
                $this->getThrottleKey($request)
            );

            return $this->respondInvalidCredentials($this->getLockoutErrorMessage($seconds));
        }

        if (!Auth::attempt($credentials)) {

            if ($throttling) {
                $this->incrementLoginAttempts($request);
            }

            return $this->respondInvalidCredentials('Invalid credentials.');
        }

        $user = Auth::user();

        try {
            // Create the user token

            $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token

            return $this->respondInternalError('Could not create token.');
        }

        // all good so return the token

        return $this->sendResponse(compact('token'));
    }

    public function authorizeAccountFacebook(AuthorizeFacebookRequest $request, SocialNetworksRepository $social, User $user) {

        $fb = $request->only('token', 'email');

        $facebook = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
        ]);

        try {

            $response = $facebook->get('/me?fields=id,name,email', $fb['token']);

            // Get profile information
            $profile = $response->getGraphUser();

            if (!isset($profile['email'])) {
                $profile['email'] = $fb['email'];
            }

            if (!$profile || !isset($profile['id'])) {
                return $this->respondInternalError('Login failed.');
            }

            // If social account is existed
            if ($social->checkAccountExist($profile['id'], 'facebook')) {

                // Get user id
                $userSocialInfo = $social->getUserSocialInfo($profile['id']);

                if ($userSocialInfo) {

                    $userInfo = $social->getUserInfo($userSocialInfo->user_id);

                    if ($userInfo) {

                        Auth::login($userInfo);

                        if (!Auth::user()) {
                            return $this->respondInternalError('Login failed.');
                        }

                        $user = Auth::user();

                        try {

                            $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                        }catch (JWTException $e) {

                            return $this->respondInternalError('Login failed.');

                        }

                        return $this->respondCreated(compact('token'), 'Login success.');

                    }

                }

            }else {

                $email = $profile['email'];

                // Check email in users table
                if ($social->checkEmailOfUser($email)) {

                    $userInfo = $social->getUserInfoWhereEmail($email);

                    if ($userInfo) {

                        if (Auth::login($userInfo)) {

                            // Store user info to social networks table
                            $fieldsSocial = array(
                                'user_id' => $userInfo->id,
                                'social_id' => $profile['id'],
                                'type' => 'facebook'
                            );

                            $social->storeAccountSocial($fieldsSocial);

                            $user = Auth::user();

                            try {

                                $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                            }catch (JWTException $e) {

                                return $this->respondInternalError('Login failed.');

                            }

                            return $this->respondCreated(compact('token'), 'Login success.');

                        }

                    }

                    return $this->respondInternalError('Login failed.');

                }else {

                    $fields = array(
                        'name' => $profile['name'],
                        'display_name' => $profile['name'],
                        'email' => $profile['email'],
                        'password' => ''
                    );

                    // Save to users table
                    $resultUsers = $user->storeSocial($fields);

                    if ($resultUsers) {

                        $credentials = $user->rewriteCredentialsSocial($fields);

                        Auth::attempt($credentials);

                        if (!Auth::user()) {
                            return $this->respondInternalError('Login failed.');
                        }

                        $user = Auth::user();

                        $fieldsSocial = array(
                            'user_id' => $user->id,
                            'social_id' => $profile['id'],
                            'type' => 'facebook'
                        );

                        // Save to social networks table
                        if (!$social->storeAccountSocial($fieldsSocial)) {
                            return $this->respondInternalError('Login failed.');
                        }

                        try {

                            $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                        }catch (JWTException $e) {

                            return $this->respondInternalError('Login failed.');

                        }

                        return $this->respondCreated(compact('token'), 'Login success.');

                    }

                }

            }

        }catch (\Exception $e) {
            return $this->respondInternalError('Login failed.');
        }

    }

    public function authorizeAccountGoogle(AuthorizeGoogleRequest $request, SocialNetworksRepository $social, User $user) {

        $google = $request->only('token');

        try {

            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            $payload = $client->verifyIdToken($google['token']);

            if (!$payload) {

                $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID_IOS')]);

                $payload = $client->verifyIdToken($google['token']);

            }

            if (!$payload || !isset($payload['sub'])) {
                return $this->respondInternalError('Login failed.');
            }

            // If social account is existed
            if ($social->checkAccountExist($payload['sub'], 'google')) {

                // Get user id
                $userSocialInfo = $social->getUserSocialInfo($payload['sub']);

                if ($userSocialInfo) {

                    $userInfo = $social->getUserInfo($userSocialInfo->user_id);

                    if ($userInfo) {

                        $userInfo = $social->getUserInfoWhereEmail($userInfo->email);

                        Auth::login($userInfo);

                        if (!Auth::user()) {
                            return $this->respondInternalError('Login failed.');
                        }

                        $user = Auth::user();

                        try {

                            $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                        }catch (JWTException $e) {

                            return $this->respondInternalError('Login failed.');

                        }

                        return $this->respondCreated(compact('token'), 'Login success.');

                    }

                }

            }else {

                $email = $payload['email'];

                // Check email in users table
                if ($social->checkEmailOfUser($email)) {

                    $userInfo = $social->getUserInfoWhereEmail($email);

                    if ($userInfo) {

                        if (Auth::login($userInfo)) {

                            // Store user info to social networks table
                            $fieldsSocial = array(
                                'user_id' => $userInfo->id,
                                'social_id' => $payload['sub'],
                                'type' => 'google'
                            );

                            $social->storeAccountSocial($fieldsSocial);

                            $user = Auth::user();

                            try {

                                $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                            }catch (JWTException $e) {

                                return $this->respondInternalError('Login failed.');

                            }

                            return $this->respondCreated(compact('token'), 'Login success.');

                        }

                    }

                    return $this->respondInternalError('Login failed.');

                }else {

                    $fields = array(
                        'name' => $payload['name'],
                        'display_name' => $payload['name'],
                        'email' => $payload['email'],
                        'password' => ''
                    );

                    // Save to users table
                    $resultUsers = $user->storeSocial($fields);

                    if ($resultUsers) {

                        $credentials = $user->rewriteCredentialsSocial($fields);

                        Auth::attempt($credentials);

                        if (!Auth::user()) {
                            return $this->respondInternalError('Login failed.');
                        }

                        $user = Auth::user();

                        $fieldsSocial = array(
                            'user_id' => $user->id,
                            'social_id' => $payload['sub'],
                            'type' => 'google'
                        );

                        // Save to social networks table
                        if (!$social->storeAccountSocial($fieldsSocial)) {
                            return $this->respondInternalError('Login failed.');
                        }

                        try {

                            $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

                        }catch (JWTException $e) {

                            return $this->respondInternalError('Login failed.');

                        }

                        return $this->respondCreated(compact('token'), 'Login success.');

                    }

                }

            }

        }catch (\Exception $e) {

            return $this->respondInternalError('Login failed.');

        }

    }

    public function create(CreateUserAccountRequest $request, User $user)
    {
        // Grab credentials from the request

        $fields = $request->only('name', 'display_name', 'email', 'password', 'password_confirmation');

        $result = $user->store($fields);

        if ($result) {
            $credentials = $user->rewriteCredentials($fields);

            Auth::attempt($credentials);

            $user = Auth::user();

            try {
                // Create the user token

                $token = JWTAuth::customClaims(['verified' => $user->verified])->fromUser($user);

            } catch (JWTException $e) {
                // something went wrong whilst attempting to encode the token

                return $this->respondInternalError('Could not create token.');
            }

            return $this->respondCreated(compact('token'), 'New user account was successfully registered.');
        }

        return $this->respondInternalError('It was not possible to create the account.');
    }

    public function refresh()
    {
        try{
            $token = JWTAuth::getToken();

            $token = JWTAuth::refresh($token);

        } catch(JWTException $e){

            return $this->respondInternalError('The token is invalid.');
        }

        return $this->sendResponse(compact('token'));
    }

    public function currentUser(Request $request, User $user)
    {
        $userId = $request->user()->id;

        $user = $user->with('community')->find($userId);

        $response = $this->processItem($user, new AuthUserTransformer, 'user');

        return $response;
    }

    /**
     * Helper method used by the ThrottlesLogins trait to construct a unique request key
     * @return string The login key used as the username
     */
    protected function loginUsername()
    {
        return $this->username;
    }

}
