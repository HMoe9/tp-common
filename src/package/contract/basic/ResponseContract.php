<?php
declare (strict_types = 1);

namespace tp\common\package\contract\basic;

use Throwable;
use think\Response as ThinkResponse;

interface ResponseContract
{
    /**
     * ajax 统一响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     * @param string $behavior
     * @return ThinkResponse
     */
    public function ajaxReturn(string $msg = 'SUCCESS', string $behavior = ''): ThinkResponse;

    /**
     * 异常处理统一响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param Throwable $e
     * @return ThinkResponse
     */
    public function exceptionReturn(Throwable $e): ThinkResponse;
}
