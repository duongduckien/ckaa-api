<?php

namespace App\Http\Requests;

use Symfony\Component\HttpFoundation\Response as IlluminateResponse;

trait ApiResponse
{
    protected $statusCode = IlluminateResponse::HTTP_OK;

    public static $CODE_WRONG_ARGS = 'GEN-FUBARGS';

    public static $CODE_NOT_FOUND = 'GEN-LIKETHEWIND';

    public static $CODE_INTERNAL_ERROR = 'GEN-AAAGGH';

    public static $CODE_UNAUTHORIZED = 'GEN-MAYBGTFO';

    public static $CODE_FORBIDDEN = 'GEN-GTFO';

    public static $CODE_INVALID_CREDENTIALS = 'GEN-NOTAUTHORIZED';

    /**
     * Getter for statusCode.
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode.
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    protected function sendResponse(array $array, array $headers = [])
    {
        $response = response()->json($array, $this->statusCode, $headers);

        return $response;
    }

    public function respondSuccess($message = 'The request was successful', $status = 'success')
    {
        $array = ['status'=>$status,'message'=>$message];

        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondWithArray($array);
    }

    public function respondFail($message = 'There was an error processing the request', $status = 'error')
    {
        $array = ['status'=>$status,'message'=>$message];

        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithArray($array);
    }

    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = response()->json($array, $this->statusCode, $headers);

        return $response;
    }

    protected function respondWithError($message, $errorCode = '')
    {
        if ($this->statusCode === IlluminateResponse::HTTP_OK) {
            trigger_error(
                'You better have a really good reason for erroring on a 200...',
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => [
                // 'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_FORBIDDEN)->respondWithError($message, self::$CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message, self::$CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondInvalidCredentials($message = 'Invalid Credentials')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError($message, self::$CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message, self::$CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError($message, self::$CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError($message, self::$CODE_WRONG_ARGS);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondCreated(array $array, $message = 'Item successfully created')
    {
        $arr['message'] = $message;
        $arr['data'] = $array;

        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respondWithArray($arr);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondAcceptedAndPending(array $array, $message = 'Item successfully created, but pending approval')
    {
        $array['message'] = $message;

        return $this->setStatusCode(IlluminateResponse::HTTP_ACCEPTED)->respondWithArray($array);
    }

    /**
     * Generates a Response when mail successfully sent with a 200 HTTP header and a given message.
     *
     * @return Response
     */
    public function respondSent(array $array, $message = 'Mail successfully sent')
    {
        $array['message'] = $message;

        return $this->setStatusCode(IlluminateResponse::HTTP_OK)->respondWithArray($array);
    }
}
