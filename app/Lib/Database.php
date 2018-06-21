<?php

namespace App\Lib;

class Database
{
    public $app;
    protected $conn;

    public function __construct($app)
    {
        $this->app = $app;
        $this->conn = $this->getConnect();
    }

    // 获取连接
    protected function getConnect() {
        if ($this->conn) {
            return $this->conn;
        }
        $db_config = $this->app->config->get('app.database.mysql');
        try {
            $options = $db_config['options'] + [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true,
            ];

            $conn = new \PDO("mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}", $db_config['user'], $db_config['password'], $options);

        }catch (\PDOException $exception) {
            throw new \PDOException($exception->getTraceAsString());
        }

        return $conn;
    }

    // 执行语句
    protected function exec($sql, $params = []) {
        try {
            $conn = $this->getConnect();
            $ret = $conn->prepare($sql);
            $ret->execute($params);
        } catch (\PDOException $exception) {
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
            return false;
        }
        return $ret;
    }


    public function select($sql, $params = []) {
        $ret = $this->exec($sql, $params);
        $ret = $ret->fetchAll();
        return $ret;
    }

    public function first($sql, $params = []) {
        $sql = strpos($sql, 'limit') > 0 ? $sql : $sql . ' limit 0,1';
        $ret = $this->exec($sql, $params);

        $rows = $ret->fetchAll();
        return count($rows) > 0 ? $rows[0] : null;
    }

    public function insert($sql, $params = []) {
        $ret = $this->exec($sql, $params);
        if (!$ret) {
            return false;
        }
        return intval($this->conn->lastInsertId());
    }

    public function update($sql, $params = []) {
        return $this->exec($sql, $params);
    }

    public function delete($sql, $params = []) {
        return $this->exec($sql, $params);
    }

    public function quote($string, $parameter_type = \PDO::PARAM_STR) {
        return $this->conn->quote($string, $parameter_type);
    }

    // 开启事务
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // 提交事务
    public function commit() {
        return $this->conn->commit();
    }

    // 回滚事务
    public function rollback() {
        return $this->conn->rollBack();
    }


}