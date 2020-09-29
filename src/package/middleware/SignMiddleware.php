<?php
declare (strict_types = 1);

namespace tp\common\package\middleware;

use tp\common\package\{
    Base,
    validate\SignValidate
};
use RuntimeException;
use InvalidArgumentException;
use think\exception\ValidateException;

class SignMiddleware
{
    use Base;

    /**
     * 参与验签的参数
     */
    protected const SIGN_PARAM = array(
        'timestamp', 'pathinfo'
    );

    /**
     * sign 校验
     * @author HMoe9 <hmoe9@qq.com>
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $validate = $this->app->make(SignValidate::class);
        $result = $validate->batch($this->app->var->isDebug)
            ->check($request->param());
        if ($result !== true)
        {
            throw new ValidateException($validate->getError());
        }

        /**
         * 1. 校验时间是否是 20s 内
         * 2. 验证 sign 的合法性
         * 3. nonce 是否已经存在 redis 中
         */
        if (abs($this->app->getBeginTime() - $request->param('timestamp')) > 20)
        {
            throw new InvalidArgumentException('TIMESTAMP_EXPIRE');
        }

        $sign = $this->getSign();
        if ($sign != $request->param('sign'))
        {
            throw new RuntimeException('SIGN_ERROR');
        }

        $key = 'sign:pathinfo:' . $request->pathinfo() .
            ':nonce:'. $request->param('nonce');
        $result = $this->app->redis->get($key);
        if (!is_null($result))
        {
            throw new InvalidArgumentException('NONCE_ALREADY_EXISTS');
        }
        $this->app->redis->set($key, 1, 120);

        return $next($request);
    }

    /**
     * 生成签名
     * @author HMoe9 <hmoe9@qq.com>
     * @return string
     */
    protected function getSign(): string
    {
        $param = $this->param;
        $param['pathinfo'] = $this->request->pathinfo();

        ksort($param);

        $buffer = '';
        foreach (self::SIGN_PARAM as $value)
        {
            $buffer .= "{$value}={$param[$value]}&";
        }

        $buffer .= $param['nonce'];
        return md5(strtoupper(md5(strtolower($buffer))));
    }
}
