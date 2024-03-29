<?php

declare(strict_types=1);

namespace Zxin\Think\Auth\Exception;

use Throwable;
use Zxin\Think\Auth\Access\Response;

class AuthorizationException extends AuthException
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: 'This action is unauthorized.', 0, $previous);

        $this->code = $code ?: 0;
    }

    /**
     * Get the response from the gate.
     *
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Set the response from the gate.
     *
     * @param  Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Create a deny response object from this exception.
     *
     * @return Response
     */
    public function toResponse()
    {
        return Response::deny($this->message, $this->code);
    }
}
