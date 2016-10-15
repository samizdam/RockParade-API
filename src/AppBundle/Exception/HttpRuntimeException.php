<?php

namespace AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Vehsamrak
 */
class HttpRuntimeException extends HttpException
{

    const HTTP_ERROR_CODE = 500;

    /**
     * @param string $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct(self::HTTP_ERROR_CODE, $message);
    }
}
