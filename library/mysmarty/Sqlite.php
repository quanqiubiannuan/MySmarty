<?php

namespace library\mysmarty;
/**
 * Date: 2019/4/16
 * Time: 16:53
 */
class Sqlite
{
    //数据库存放文件夹
    private $databaseDir = CONFIG['database']['sqlite']['database_dir'];
    //数据库名
    private $database = CONFIG['database']['sqlite']['database'];
    //数据库表名
    private $table = CONFIG['database']['sqlite']['table'];


    private static $obj;
    private $dbs = [];
    private $db;

    private $mWheres = [];
    private $mWhere = '';
    private $mBindArgs = [];
    private $mField = '*';
    private $mLimit = -1;
    private $mOffset = -1;
    private $mOrder = '';
    private $mGroupBy = '';
    private $mHaving = '';
    private $mJoin = [];

    /**
     * 初始化条件
     */
    private function init()
    {
        $this->mWheres = [];
        $this->mBindArgs = [];
        $this->mField = '*';
        $this->mLimit = -1;
        $this->mOffset = -1;
        $this->mOrder = '';
        $this->mGroupBy = '';
        $this->mHaving = '';
        $this->mJoin = [];
    }

    private function __construct()
    {
        if (!file_exists($this->databaseDir)) {
            mkdir($this->databaseDir, 0777, true);
        }
    }

    private function __clone()
    {
    }

    /**
     *  连接数据库
     */
    private function connect()
    {
        $key = $this->databaseDir . $this->database;
        if (!isset($this->dbs[$key])) {
            $this->databaseDir = rtrim($this->databaseDir, '/');
            $this->db = new \SQLite3($this->databaseDir . '/' . $this->database . '.db');
            $this->dbs[$key] = $this->db;
        } else {
            $this->db = $this->dbs[$key];
        }
        $this->db->enableExceptions(true);
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
     * 获取单一对象实例
     * @return Sqlite
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 执行sql语句
     * @param string $sql sql语句
     * @return bool
     */
    public function exec($sql)
    {
        try {
            $this->getDb()->exec($sql);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 获取sqlite执行对象
     * @return SQLite3
     */
    public function getDb()
    {
        $this->connect();
        return $this->db;
    }

    /**
     * 获取上次错误信息
     * @return string
     */
    public function getLastErrorMsg()
    {
        return $this->getDb()->lastErrorMsg();
    }

    /**
     * 获取上次错误码
     * @return int
     */
    public function getLastErrorCode()
    {
        return $this->getDb()->lastErrorCode();
    }

    /**
     * 获取上次添加的id
     * @return int
     */
    public function getLastInsertRowID()
    {
        return $this->getDb()->lastInsertRowID();
    }

    /**
     * 添加数据
     * @param array $data 键值对数据
     * @return bool|int -1 出现异常 0 添加失败 >0 添加后返回id
     */
    public function insert($data)
    {
        if (empty($data) && !is_array($data)) {
            return false;
        }
        $keys = array_keys($data);
        $values = array_values($data);
        $params = array_fill(0, count($keys), '?');
        try {
            $sql = 'insert into ' . $this->table . ' (' . implode(',', $keys) . ') values (' . implode(',', $params) . ')';
            $stmt = $this->getDb()->prepare($sql);
            foreach ($values as $k => $v) {
                $stmt->bindValue($k + 1, $v);
            }
            $result = $stmt->execute();
            if ($result) {
                return $this->getDb()->lastInsertRowID();
            }
        } catch (\Exception $e) {
            return -1;
        }
        return 0;
    }

    /**
     * 执行查询语句
     * @param string $sql 执行语句
     * @param array $bindArgs 绑定参数
     * @return array|bool false 出现异常
     */
    public function query($sql, $bindArgs = [])
    {
        try {
            $stmt = $this->getDb()->prepare($sql);
            if (!empty($bindArgs)) {
                foreach ($bindArgs as $k => $v) {
                    $stmt->bindValue($k + 1, $v);
                }
            }
            $result = $stmt->execute();
            $data = [];
            while (true) {
                $tmp = $result->fetchArray(SQLITE3_ASSOC);
                if ($tmp === false) {
                    break;
                }
                $data[] = $tmp;
            }
            $this->init();
            $result->finalize();
            return $data;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * where条件
     * @param string $field 字段
     * @param null $value 值
     * @param string $op 操作符
     * @return $this
     */
    public function where($field, $value = null, $op = '=')
    {
        $this->mWheres[] = [$field, $value, $op, 'and'];
        return $this;
    }

    /**
     * where条件 or查询
     * @param string $field 字段
     * @param null $value 值
     * @param string $op 操作符
     * @return $this
     */
    public function whereOr($field, $value = null, $op = '=')
    {
        $this->mWheres[] = [$field, $value, $op, 'or'];
        return $this;
    }

    /**
     * 等于
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function eq($field, $value)
    {
        return $this->where($field, $value, '=');
    }

    /**
     * 大于
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function gt($field, $value)
    {
        return $this->where($field, $value, '>');
    }

    /**
     * 小于
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function lt($field, $value)
    {
        return $this->where($field, $value, '<');
    }


    /**
     * 不相等查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function neq($field, $value)
    {
        $this->where($field, $value, '!=');
        return $this;
    }


    /**
     * 大于或等于查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function egt($field, $value)
    {
        $this->where($field, $value, '>=');
        return $this;
    }

    /**
     * 小于或等于查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function elt($field, $value)
    {
        $this->where($field, $value, '<=');
        return $this;
    }

    /**
     * 相似查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function like($field, $value)
    {
        $this->where($field, $value, 'like');
        return $this;
    }

    /**
     * 不相似查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function notLike($field, $value)
    {
        $this->where($field, $value, 'not like');
        return $this;
    }

    /**
     * 区间查询
     *
     * @param string $field
     *            字段
     * @param string $startValue
     *            开始值
     * @param string $endValue
     *            结束值
     * @return $this
     */
    public function between($field, $startValue, $endValue)
    {
        $this->where($field . ' between "' . $startValue . '" and "' . $endValue . '"');
        return $this;
    }

    /**
     * 不在区间查询
     *
     * @param string $field
     *            字段
     * @param string $startValue
     *            开始值
     * @param string $endValue
     *            结束值
     * @return $this
     */
    public function notBetween($field, $startValue, $endValue)
    {
        $this->where($field . ' not between "' . $startValue . '" and "' . $endValue . '"');
        return $this;
    }

    /**
     * in查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值，逗号分割，或数组
     * @return $this
     */
    public function in($field, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->where($field . ' in (' . $value . ')');
        return $this;
    }

    /**
     * not in查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值，逗号分割，或数组
     * @return $this
     */
    public function notIn($field, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $this->where($field . ' not in (' . $value . ')');
        return $this;
    }

    /**
     * find_in_set查询
     *
     * @param string $field
     *            字段
     * @param string $value
     *            值
     * @return $this
     */
    public function findInSet($field, $value)
    {
        $this->where('find_in_set("' . $value . '",' . $field . ')');
        return $this;
    }

    /**
     * 查询匹配的字段数据
     * @param string $feild
     * @param string $value
     * @return $this
     */
    public function glob($feild, $value)
    {
        return $this->where($feild, $value, 'glob');
    }

    /**
     * 解决where条件
     */
    private function dealWhere()
    {
        if (!empty($this->mWheres)) {
            $where = '';
            $bindArgs = [];
            foreach ($this->mWheres as $v) {
                if ($v[1] === null) {
                    $tmp = $v[0];
                } else {
                    $tmp = $v[0] . ' ' . $v[2] . ' ?';
                    $bindArgs[] = $v[1];
                }
                if (empty($where)) {
                    $where = $tmp;
                } else {
                    $where .= ' ' . $v[3] . ' ' . $tmp;
                }
            }
            if (!empty($where)) {
                $this->mWhere = 'where ' . $where;
                $this->mBindArgs = $bindArgs;
            }
        }
    }

    /**
     * 设置查询字段
     * @param array|string $fields
     * @return $this
     */
    public function field($fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $this->mField = $fields;
        return $this;
    }

    /**
     * group by
     * @param string $field
     * @return $this
     */
    public function groupBy($field)
    {
        $this->mGroupBy = $field;
        return $this;
    }

    /**
     * @param string $having
     * @return $this
     */
    public function having($having)
    {
        $this->mHaving = $having;
        return $this;
    }

    /**
     * 查询数据
     * @return array|bool
     */
    public function select()
    {
        $this->dealWhere();
        $sql = 'select ' . $this->mField . ' from ' . $this->table;
        if (!empty($this->mJoin)) {
            foreach ($this->mJoin as $v) {
                switch ($v[2]) {
                    case 'cross':
                        $sql .= ' cross join ' . $v[0] . ' on ' . $v[1];
                        break;
                    case 'inner':
                        $sql .= ' inner join ' . $v[0] . ' on ' . $v[1];
                        break;
                    case 'outer':
                        $sql .= ' left outer join ' . $v[0] . ' on ' . $v[1];
                        break;
                }
            }
        }
        if (!empty($this->mWhere)) {
            $sql .= ' ' . $this->mWhere;
        }
        if (!empty($this->mGroupBy)) {
            $sql .= ' group by ' . $this->mGroupBy;
        }
        if (!empty($this->mHaving)) {
            $sql .= ' having ' . $this->mHaving;
        }
        if (!empty($this->mOrder)) {
            $sql .= ' ' . $this->mOrder;
        }
        if ($this->mLimit > 0) {
            $sql .= ' LIMIT ' . $this->mLimit;
        }
        if ($this->mOffset >= 0) {
            $sql .= ' OFFSET ' . $this->mOffset;
        }
        return $this->query($sql, $this->mBindArgs);
    }

    /**
     * limit条件
     * @param integer $offset
     * @param null|integer $limit
     * @return $this
     */
    public function limit($offset, $limit = null)
    {
        if ($limit === null) {
            $this->mLimit = $offset;
        } else {
            $this->mOffset = $offset;
            $this->mLimit = $limit;
        }
        return $this;
    }

    /**
     * 查询一条记录
     * @return array|mixed
     */
    public function find()
    {
        $this->limit(0, 1);
        $data = $this->select();
        if (!empty($data) && isset($data[0])) {
            return $data[0];
        }
        return [];
    }

    /**
     * 排序
     * @param string $field
     * @param null|string $order
     * @return $this
     */
    public function order($field, $order = null)
    {
        $tmp = $this->mOrder;
        if ($order === null) {
            if (empty($tmp)) {
                $tmp = 'order by ' . $field;
            } else {
                $tmp .= ',' . $field;
            }
        } else {
            if (empty($tmp)) {
                $tmp = 'order by ' . $field . ' ' . $order;
            } else {
                $tmp .= ',' . $field . ' ' . $order;
            }
        }
        $this->mOrder = $tmp;
        return $this;
    }

    /**
     * 关闭数据库连接
     */
    public function close()
    {
        if ($this->getDb()) {
            $this->getDb()->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * 清空表数据
     * @param string $table
     * @return bool
     */
    public function truncate($table = '')
    {
        if (empty($table)) {
            $table = $this->table;
        }
        return $this->exec('delete from ' . $table);
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        $this->dealWhere();
        $sql = 'delete from ' . $this->table;
        if (!empty($this->mWhere)) {
            $sql .= ' ' . $this->mWhere;
        }
        $result = $this->query($sql, $this->mBindArgs);
        return $result === false;
    }

    /**
     * 更新数据
     * @param array $data 键值对数据
     * @return bool|int -1 出现异常  false 失败  true 更新成功
     */
    public function update($data)
    {
        if (empty($data) && !is_array($data)) {
            return false;
        }
        $this->dealWhere();
        $sql = 'update ' . $this->table . ' set';
        $tmp = [];
        $values = array_values($data);
        foreach ($data as $k => $v) {
            $tmp[] = $k . '=?';
        }
        $sql .= ' ' . implode(',', $tmp) . ' ' . $this->mWhere;
        $values = array_merge($values, $this->mBindArgs);
        try {
            $stmt = $this->getDb()->prepare($sql);
            foreach ($values as $k => $v) {
                $stmt->bindValue($k + 1, $v);
            }
            $result = $stmt->execute();
            $this->init();
            if ($result) {
                return true;
            }
        } catch (\Exception $e) {
            return -1;
        }
        return false;
    }

    /**
     * join条件查询
     * @param string $table 表名
     * @param string $condition 条件
     * @param string $type cross 交叉连接，inner 内连接，outer 外连接
     * @return $this
     */
    public function join($table, $condition, $type = 'outer')
    {
        $this->mJoin[] = [$table, $condition, $type];
        return $this;
    }

    /**
     * 交叉连接
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function crossJoin($table, $condition)
    {
        return $this->join($table, $condition, 'cross');
    }

    /**
     * 内连接
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function innerJoin($table, $condition)
    {
        return $this->join($table, $condition, 'inner');
    }

    /**
     * 外连接
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function outerJoin($table, $condition)
    {
        return $this->join($table, $condition, 'outer');
    }

    /**
     * 获取所有表的结构
     * @return array|bool
     */
    public function getAllTableInfo()
    {
        return $this->query('select * from sqlite_master where type="table"');
    }

    /**
     * 获取所有的表名
     * @return array
     */
    public function getAllTableNames()
    {
        $tmp = [];
        $info = $this->getAllTableInfo();
        if (!is_array($info)) {
            return $tmp;
        }
        foreach ($info as $v) {
            $tmp[] = $v['name'];
        }
        return $tmp;
    }

    /**
     * 获取表的创建语句
     * @param string $table
     * @return mixed|string
     */
    public function getTableCreateSql($table = '')
    {
        if (empty($table)) {
            $table = $this->table;
        }
        $sql = '';
        $info = $this->getAllTableInfo();
        if (!is_array($info)) {
            return $sql;
        }
        foreach ($info as $v) {
            if ($v['name'] === $table) {
                $sql = $v['sql'];
                break;
            }
        }
        return $sql;
    }

    /**
     * 设置sqlite数据文件所在文件夹
     * @param string $databaseDir
     * @return $this
     */
    public function setDatabaseDir($databaseDir){
        $this->databaseDir = $databaseDir;
        return $this;
    }
}