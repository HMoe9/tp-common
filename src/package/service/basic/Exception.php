<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use tp\common\package\contract\basic\ExceptionContract;

class Exception implements ExceptionContract
{
    use Base;

    /**
     * 错误信息
     * @var array
     */
    protected $lang = [];

    /**
     * 加载语言包
     * @author HMoe9 <hmoe9@qq.com>
     */
    protected function initialize(): void
    {
        $langSet = $this->app->lang->getLangSet();
        $files = $this->app->var->$langSet;
        foreach ($files as $file)
        {
            // 加载 package 内的语言包
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $this->lang += $this->app->lang->get($filename);

            // 加载模块中自定义的语言包
            $module_lang = $this->app->getAppPath() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . $filename . '.php'; // 模块的语言包
            if (is_file($module_lang))
            {
                $result = $this->parse($module_lang);
                $this->lang += $result;
            }
        }
    }

    /**
     * 获取错误码
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $message
     * @return string
     */
    public function getCode(string $message): string
    {
        if (empty($this->lang) ||
            !array_key_exists($message, $this->lang))
        {
            return self::ERROR_CODE;
        }

        return $this->lang[$message];
    }

    /**
     * 获取错误信息
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $code
     * @return string
     */
    public function getMessage(string $code='10000'): string
    {
        if (!is_numeric($code))
        {
            $code = $this->getCode($code);
        }

        if (empty($this->lang) ||
            !array_key_exists($code, $this->lang))
        {
            return self::ERROR_MSG;
        }

        return $this->lang[$code];
    }

    /**
     * 解析语言文件
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $file
     * @return array
     */
    protected function parse(string $file): array
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);

        switch ($type) {
            case 'php':
                $result = include $file;
                break;
        }

        return isset($result) && is_array($result) ? $result : [];
    }
}
