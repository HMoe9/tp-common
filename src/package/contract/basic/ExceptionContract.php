<?php
declare (strict_types = 1);

namespace tp\common\package\contract\basic;

interface ExceptionContract
{
    /**
     * 默认错误码,必须是字符串,用于查询错误信息。实际反给前端时会改成 int
     * @var string
     */
    public const ERROR_CODE = '-1';

    /**
     * 默认错误信息
     * @var string
     */
    public const ERROR_MSG = 'UNKNOWN ERROR';

    /**
     * 获取错误码
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $message
     * @return string
     */
    public function getCode(string $message): string;

    /**
     * 获取错误信息
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $code
     * @return string
     */
    public function getMessage(string $code='10000'): string;
}
