<?php
declare (strict_types = 1);

namespace tp\common\package\exception;

use RuntimeException;
use Exception;

/**
 * Class HttpExceptions
 * HTTP 异常重写
 * @author HMoe9 <hmoe9@qq.com>
 * @package tp\common\package\exception
 */
class HttpExceptions extends RuntimeException
{
    protected $statusCode;
    protected $headers;
    protected $data;

    public function __construct(string $message = '', array $data = array(), int $statusCode = 500, int $code = 500, Exception $previous = null, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
        $this->data       = $data;

        parent::__construct($message, $code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
