<?php
declare (strict_types=1);

namespace tp\common\package\service;

use think\Db;
use tp\common\package\contract\DatabaseContract;

class Database implements DatabaseContract
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * 表名
     * @var string
     */
    protected $table;

    public function __construct(Db $db, $table)
    {
        $this->db    = $db;
        $this->table = $table;
    }

    public static function __make(Db $db, $table)
    {
        return new self($db, $table);
    }

    /**
     * 日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $data
     */
    public function log(array $data): void
    {
        $exist = app('config')->get('tp-common.table_exist_verify', false);
        $instance = $exist ? $this->getTableExists() : $this->getTable();
        $instance && $instance->insert($data);
    }

    protected function getTable()
    {
        return $this->db->name($this->table);
    }

    /**
     * 写日志前判断表是否存在
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    protected function getTableExists()
    {
        $prefix = $this->db->connect()->getConfig('prefix'); // 获取表前缀
        $state = $this->db->query("show tables like '{$prefix}{$this->table}'"); // 判断表是否存在
        return empty($state) ? false : $this->db->name($this->table);
    }
}