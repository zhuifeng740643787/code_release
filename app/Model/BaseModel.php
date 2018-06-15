<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/13
 * Time: 上午9:33
 */

namespace App\Model;

abstract class BaseModel
{

    protected $table;
    protected $db;
    protected static $instances;

    const ENABLE = 1;
    const DISABLE = 0;

    public function __construct()
    {
        $this->db = app()->db;
    }

    public static function allEnables() {
        return static::getInstance()->select('*', 'status=:STATUS', [':STATUS' => static::ENABLE]);
    }

    public static function findEnable($id) {
        return static::getInstance()->first('*', 'id=:ID', [':ID' => $id]);
    }

    public function select($select = '*', $where = '', $params = [])
    {
        $sql = "select $select from {$this->table}";
        if ($where) {
            $sql .= " where $where";
        }
        return $this->db->select($sql, $params);
    }

    public function first($select = '*', $where = '', $params = [])
    {
        $sql = "select $select from {$this->table}";
        if ($where) {
            $sql .= " where $where";
        }
        return $this->db->first($sql, $params);
    }

    public function update($sets, $where = '', $params = [])
    {
        $set_str = '';
        if (is_array($sets)) {
            foreach ($sets as $k => $v) {
                $set_str .= $k . '=' . is_string($v) ? "'$v'" : $v . ',';
            }
        } else {
            $set_str = $sets;
        }
        $set_str = rtrim($set_str, ',');
        if (empty($set_str)) {
            return false;
        }

        $sql = "update {$this->table}";
        if ($where) {
            $sql .= " where $where";
        }
        return $this->db->update($sql, $params);
    }

    public static function getInstance()
    {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = new static();
        }

        return static::$instances[static::class];
    }


    public static function __callStatic($name, $arguments)
    {
        $model = static::getInstance();
        return call_user_func_array([$model, $name], $arguments);
    }

}