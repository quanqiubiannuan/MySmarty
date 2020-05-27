<?php

namespace library\mysmarty;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

/**
 * 需要安装mongodb扩展
 * 参考：https://www.php.net/manual/zh/mongodb.installation.php
 *
 * @author 戴记
 *
 */
class Mongodb
{

    private static $obj = null;

    private $manager;

    private $database;

    private $table;

    private $mWhere = [];

    private $mType = [];

    private $mLimit = -1;

    private $mSkip = -1;

    private $mOrder = [];

    private $mMajority = 1000;

    private function init()
    {
        $this->mWhere = [];
        $this->mType = [];
        $this->mLimit = -1;
        $this->mSkip = -1;
        $this->mOrder = [];
        $this->mMajority = 1000;
    }

    /**
     * 设置WriteConcern::MAJORITY
     *
     * @param int $mMajority
     * @return $this
     */
    public function setMMajority($mMajority)
    {
        $this->mMajority = $mMajority;
        return $this;
    }

    private function __construct()
    {
        $this->connect();
        $this->database = config('database.mongodb.database');
        $this->table = config('database.mongodb.table');
    }

    private function __clone()
    {
    }

    /**
     * 获取对象实例
     *
     * @return Mongodb
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    private function connect()
    {
        if ($this->manager === null) {
            $this->manager = new Manager(config('database.mongodb.uri'), config('database.mongodb.uri_options'), config('database.mongodb.driver_options'));
        }
        return $this->manager;
    }

    /**
     * 指定数据库名
     *
     * @param string $database
     *            数据库名
     * @return $this
     */
    public function name($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * 指定表名
     *
     * @param string $table
     *            表名
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 添加数据
     *
     * @param array $document
     * @return int|string|bool
     */
    public function add($document)
    {
        $bulk = new BulkWrite();
        $bulk->insert($document);
        return $this->executeBulk($bulk);
    }

    /**
     * 查询
     *
     * @return string[]
     */
    public function query()
    {
        $filter = $this->mWhere;
        if (!empty($this->mType)) {
            foreach ($this->mType as $field => $value) {
                if (!isset($filter[$field]) || is_array($filter[$field])) {
                    $filter[$field]['$type'] = $value;
                }
            }
        }
        $options = [];
        if ($this->mLimit > 0) {
            $options['limit'] = $this->mLimit;
        }

        if ($this->mSkip >= 0) {
            $options['skip'] = $this->mSkip;
        }

        if (!empty($this->mOrder)) {
            $options['sort'] = $this->mOrder;
        }

        $query = new Query($filter, $options);
        $cursor = $this->manager->executeQuery($this->database . '.' . $this->table, $query);
        $data = [];
        foreach ($cursor as $document) {
            $tmp = (array)$document;
            if (is_object($tmp['_id'])) {
                $tmp['_id'] = strval($tmp['_id']);
            }
            $data[] = $tmp;
        }
        $this->init();
        return $data;
    }

    /**
     * where条件
     *
     * @param string $field
     * @param string $value
     * @param string $op
     * @param boolean $and
     * @return $this
     */
    public function where($field, $value = null, $op = '=', $and = TRUE)
    {
        $where = [];
        switch ($op) {
            case '=':
                $where[$field] = $value;
                break;
            case '<':
                $where[$field] = [
                    '$lt' => $value
                ];
                break;
            case '<=':
                $where[$field] = [
                    '$lte' => $value
                ];
                break;
            case '>':
                $where[$field] = [
                    '$gt' => $value
                ];
                break;
            case '>=':
                $where[$field] = [
                    '$gte' => $value
                ];
                break;
            case '!=':
                $where[$field] = [
                    '$ne' => $value
                ];
                break;
        }
        if ($and) {
            $this->mWhere = $where;
        } else {
            $this->mWhere['$or'][] = $where;
        }
        return $this;
    }

    /**
     * $type 操作符
     *
     * @param string $field
     * @param string|int $type
     * @return $this
     */
    public function fieldType($field, $type = 2)
    {
        $this->mType[$field] = $type;
        return $this;
    }

    /**
     * 限制条件
     *
     * @param integer $offset
     *            偏移数量
     * @param integer $size
     *            限制大小
     * @return $this
     */
    public function limit($offset, $size = -1)
    {
        if ($size <= 0) {
            $this->mLimit = $offset;
        } else {
            $this->mLimit = $size;
            $this->mSkip = $offset;
        }

        return $this;
    }

    /**
     * 排序
     *
     * @param string $field
     *            排序字段
     * @param integer|string $order
     *            1 升序 -1降序
     * @return $this
     */
    public function order($field, $order = 1)
    {
        if (is_string($order)) {
            switch ($order) {
                case 'desc':
                    $order = -1;
                    break;
                case 'asc':
                    $order = 1;
                    break;
            }
        }
        $this->mOrder = [
            $field => $order
        ];
        return $this;
    }

    /**
     * 更新数据
     *
     * @param array $data
     *            需要更新的数据
     * @param array $whereArgs
     *            更新条件
     * @return boolean
     */
    public function update($data, $whereArgs)
    {
        $bulk = new BulkWrite();
        $bulk->update($whereArgs, [
            '$set' => $data
        ], [
            'multi' => false,
            'upsert' => false
        ]);
        return $this->executeBulk($bulk);
    }

    /**
     * 执行一个bulk
     *
     * @param BulkWrite $bulk
     * @return boolean
     */
    public function executeBulk($bulk)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, $this->mMajority);
        try {
            $this->manager->executeBulkWrite($this->database . '.' . $this->table, $bulk, $writeConcern);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 获取MongoDB Manager对象
     *
     * @return mixed
     */
    public function getMongoDBManager()
    {
        return $this->manager;
    }

    /**
     * 删除数据
     *
     * @param array $whereArgs
     *            删除条件
     * @param bool $limit
     *            删除条数，false 删除所有,true 删除一条
     * @return boolean
     */
    public function delete($whereArgs, $limit = FALSE)
    {
        $bulk = new BulkWrite();
        $bulk->delete($whereArgs, [
            'limit' => $limit
        ]);
        return $this->executeBulk($bulk);
    }

    /**
     * 查询一条数据
     *
     * @return array|string
     */
    public function find()
    {
        $data = $this->query();
        if (empty($data)) {
            return [];
        }
        return $data[0];
    }

    /**
     * 查询所有
     *
     * @return array
     */
    public function select()
    {
        return $this->query();
    }

    /**
     * 删除所有文档
     *
     * @return boolean
     */
    public function truncate()
    {
        $command = new Command([
            'drop' => $this->table
        ]);
        try {
            $this->manager->executeCommand($this->database, $command);
        } catch (\Exception $e) {
            return FALSE;
        }
        return true;
    }
}