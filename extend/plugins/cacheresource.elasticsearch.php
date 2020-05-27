<?php

use library\mysmarty\ElasticSearch;

class Smarty_CacheResource_Elasticsearch extends Smarty_CacheResource_Custom
{

    private $database;

    private $table;

    public function __construct()
    {
        $this->database = config('smarty.caching_type_params.elasticsearch.database');
        $this->table = config('smarty.caching_type_params.elasticsearch.table');
    }

    /**
     * 获取缓存的内容及修改时间
     *
     * @param string $id
     *            缓存内容的唯一识别ID
     * @param string $name
     *            模板名称
     * @param string $cache_id
     *            缓存ID
     * @param string $compile_id
     *            编译ID
     * @param string $content
     *            （引用的）缓存内容
     * @param integer $mtime
     *            缓存修改的时间戳 (epoch)
     * @return void
     */
    protected function fetch($id, $name, $cache_id, $compile_id, &$content, &$mtime)
    {
        $data = ElasticSearch::getInstance()->setDataBase($this->database)
            ->setTable($this->table)
            ->field('content,modified')
            ->where('id', $id)
            ->find();
        if (!empty($data)) {
            $content = $data['content'];
            $mtime = $data['modified'];
        } else {
            $content = null;
            $mtime = null;
        }
    }

    /**
     * 保存缓存内容
     *
     * @param string $id
     *            缓存内容的唯一识别ID
     * @param string $name
     *            模板名称
     * @param string $cache_id
     *            缓存ID
     * @param string $compile_id
     *            编译ID
     * @param integer|null $exp_time
     *            缓存过期时间，或null
     * @param string $content
     *            需要缓存的内容
     * @return boolean 成功true，失败false
     */
    protected function save($id, $name, $cache_id, $compile_id, $exp_time, $content)
    {
        $result = ElasticSearch::getInstance()->setDataBase($this->database)
            ->setTable($this->table)
            ->insert([
                'id' => $id,
                'name' => $name,
                'cache_id' => $cache_id,
                'compile_id' => $compile_id,
                'content' => $content,
                'modified' => time()
            ]);
        return $result > 0;
    }

    /**
     * 删除缓存
     *
     * @param string $name
     *            模板名称
     * @param string $cache_id
     *            缓存ID
     * @param string $compile_id
     *            编译ID
     * @param integer|null $exp_time
     *            缓存过期时间，或null
     * @return integer 返回被删除的缓存数量
     */
    protected function delete($name, $cache_id, $compile_id, $exp_time)
    {
        // 删除整个缓存
        if ($name === null && $cache_id === null && $compile_id === null && $exp_time === null) {
            // 返回删除缓存记录的数量，需要再进行一次查询来计算。
            ElasticSearch::getInstance()->setDataBase($this->database)
                ->setTable($this->table)
                ->truncate();
            return -1;
        }
        $model = ElasticSearch::getInstance()->setDataBase($this->database)->setTable($this->table);
        if ($name !== null) {
            $model = $model->where('name', $name);
        }
        // 匹配编译ID
        if ($compile_id !== null) {
            $model = $model->where('compile_id', $compile_id);
        }
        // 匹配过期时间范围
        if ($exp_time !== null) {
            $model = $model->where('modified', intval($exp_time), '<');
        }
        // 匹配缓存ID和缓存组的子ID
        if ($cache_id !== null) {
            $model = $model->prefix('cache_id', $cache_id);
        }
        // 执行删除
        return $model->del();
    }

    /**
     * 获取缓存的修改时间,必须实现这个接口
     *
     * @note 这是个可选的实现接口。在你确定仅获取修改时间会比获取整个内容要更快的时候，使用此接口。
     *
     * @param string $id
     *            缓存内容的唯一识别ID
     * @param string $name
     *            模板名称
     * @param string $cache_id
     *            缓存ID
     * @param string $compile_id
     *            编译ID
     * @return integer|boolean 返回模板修改时间，如果找不到缓存则返回false
     */
    protected function fetchTimestamp($id, $name, $cache_id, $compile_id)
    {
        $data = ElasticSearch::getInstance()->setDataBase($this->database)
            ->setTable($this->table)
            ->field('modified')
            ->where('id', $id)
            ->find();
        if (!empty($data)) {
            return $data['modified'];
        }
        return false;
    }
}