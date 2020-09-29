<?php
declare (strict_types = 1);

namespace tp\common\package\validate;

use think\Validate;

class SignValidate extends Validate
{
    /**
     * 定义验证规则
     * @var array
     */
    protected $rule = array(
        'sign' => array('require', 'length' => '32', ),
        'timestamp' => array('require', 'integer', ),
        'nonce' => array('require', 'length' => '16', 'alphaNum', ),
    );

    /**
     * 定义错误信息
     * @var array
     */
    protected $message = array(
        'sign' => array('require' => 'SIGN_REQUIRE', 'length' => 'SIGN_LENGTH_ERROR', ),
        'timestamp' => array('require' => 'TIMESTAMP_REQUIRE', 'integer' => 'TIMESTAMP_TYPE_FAIL', ),
        'nonce' => array('require' => 'NONCE_REQUIRE', 'length' => 'NONCE_LENGTH_ERROR', 'alphaNum' => 'NONCE_TYPE_FAIL', ),
    );
}
