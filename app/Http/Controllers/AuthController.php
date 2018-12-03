<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Http\Requests\AuthorizeRequest;
use App\Api\Models\User;
use Auth;
use JWTAuth;
use League\Flysystem\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthController extends ApiController {

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

    public function __construct() {

        $this->middleware('jwt.auth', ['only' => ['currentUser']]);

        $this->lockoutTime = config('api.login.lockout_time', $this->lockoutTime);

        $this->maxLoginAttempts = config('api.login.max_login_attemps', $this->maxLoginAttempts);

        parent::__construct();

    }

    public function authorize(AuthorizeRequest $request, User $user) {

        die('ddadasdasdas');

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

    public function create(CreateUserAccountRequest $request, User $user) {

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

    public function refresh() {

        try{
            $token = JWTAuth::getToken();

            $token = JWTAuth::refresh($token);

        } catch(JWTException $e){

            return $this->respondInternalError('The token is invalid.');
        }

        return $this->sendResponse(compact('token'));

    }

    public function currentUser(Request $request, User $user) {

        $userId = $request->user()->id;

        $user = $user->with('community')->find($userId);

        $response = $this->processItem($user, new AuthUserTransformer, 'user');

        return $response;

    }

    /**
     * Helper method used by the ThrottlesLogins trait to construct a unique request key
     * @return string The login key used as the username
     */
    protected function loginUsername() {
        return $this->username;
    }
}
