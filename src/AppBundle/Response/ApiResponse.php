<?php

namespace AppBundle\Response;

use AppBundle\Response\Infrastructure\AbstractApiResponse;

/**
 * @author Vehsamrak
 */
class ApiResponse extends AbstractApiResponse
{
    protected $data;
    
    public function __construct($data, int $httpCode)
    {
        $this->data = $data;
        $this->httpCode = $httpCode;
    }
}
