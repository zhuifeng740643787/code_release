<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/13
 * Time: 上午9:33
 */

namespace App\Model;

use App\Lib\Log;

abstract class BaseModel
{
    protected $primary_key = 'id'; // 主键
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

    public static function inIdsEnables($ids = [], $id_name = null)
    {
        if (empty($ids)) {
            return [];
        }
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $enable = static::ENABLE;
        $model = static::getInstance();
        $id_name = empty($id_name) ? $model->primary_key : $id_name;
        return $model->select('*', "status=$enable and $id_name in ($in)", $ids);
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