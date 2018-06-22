<?php

/**
 * Created by PhpStorm.
 * User: gongyidong
 * Date: 2018/6/13
 * Time: 上午9:33
 */

namespace App\Model;

use App\Lib\Database;

abstract class BaseModel
{
    protected $primary_key = 'id'; // 主键
    protected $table;
    /**
     * @var Database
     */
    protected $db;
    protected static $instances;
    protected $attributes = []; // 参数

    const ENABLE = 1;
    const DISABLE = 0;


    public function __construct()
    {
        $this->db = app()->db;
    }

    public static function allEnables()
    {
        return static::getInstance()->select('*', 'status=:STATUS', [':STATUS' => static::ENABLE]);
    }

    public static function inIdsEnables($ids = [], $id_name = null)
    {
        if (empty($ids)) {
            return [];
        }
        $ids = array_values($ids);
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $enable = static::ENABLE;
        $model = static::getInstance();
        $id_name = empty($id_name) ? $model->primary_key : $id_name;
        return $model->select('*', "status=$enable and $id_name in ($in)", $ids);
    }

    public static function findEnable($id)
    {
        return static::getInstance()->first('*', 'id=:ID', [':ID' => $id]);
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

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->attributes[$name] = $value;
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        throw new \Exception('未定义的参数' . $name);
    }

    // 编辑数据
    public function save()
    {
        // 插入
        $params = $this->getAttributes();
        // 插入
        if (empty($params[$this->primary_key])) {
            $id = $this->insert($params);
            if (!$id) {
                throw new \Exception('插入失败');
            }
            $this->attributes[$this->primary_key] = $id;
        } else {
            // 编辑
            $id = $params[$this->primary_key];
            unset($params[$this->primary_key]);
            $this->update($params, "{$this->primary_key}=:ID", [':ID' => $id]);
        }

        return $this;
    }

    /**
     * 添加
     * @param array $params
     * @return static
     */
    public static function add($params = []) {
        $model = new static();
        foreach ($params as $key => $value) {
            $model->{$key} = $value;
        }
        $model->save();

        return $model;
    }

    /**
     * 查询
     * @param string $select
     * @param string $where
     * @param array $params
     * @return array
     */
    protected function select($select = '*', $where = '', $params = [])
    {
        $sql = "select $select from {$this->table}";
        if ($where) {
            $sql .= " where $where";
        }
        $rows = $this->db->select($sql, $params);
        // 映射为当前类
        return array_map(function($item) {
            return cast(get_class($this), $item);
        }, $rows);
    }

    /**
     * @param string $select
     * @param string $where
     * @param array $params
     * @return object self
     */
    protected function first($select = '*', $where = '', $params = [])
    {
        $sql = "select $select from {$this->table}";
        if ($where) {
            $sql .= " where $where";
        }
        $row = $this->db->first($sql, $params);
        if ($row) {
            $row = cast(get_class($this), $row);
        }
        return $row;
    }

    /**
     * 修改
     * @param string|array $sets 要修改的参数
     * @param string $where
     * @param array $params
     * @return bool
     */
    protected function update($sets, $where = '', $params = [])
    {
        $set_str = '';
        if (is_array($sets)) {
            foreach ($sets as $k => $v) {
                if ($k === 'created_at' || $k === 'updated_at') {
                    continue;
                }
                $place = ":SET_{$k}";
                $set_str .= $k . "={$place},";
                $params[$place] = $v;
            }
        } else {
            $set_str = $sets;
        }

        $set_str = rtrim($set_str, ',');
        if (empty($set_str)) {
            return false;
        }

        $sql = "update {$this->table} set $set_str ";
        if ($where) {
            $sql .= " where $where";
        }
        return $this->db->update($sql, $params);
    }

    protected function insert($params)
    {
        if (empty($params) || !is_array($params)) {
            throw new \Exception('插入的参数有误');
        }
        $columns = array_keys($params);
        $params = array_values($params);
        $columns_space = implode(',', $columns);
        $params_space = str_repeat('?,', count($params) - 1) . '?';
        $sql = "insert into {$this->table}($columns_space) values ($params_space)";
        return $this->db->insert($sql, $params);
    }


}