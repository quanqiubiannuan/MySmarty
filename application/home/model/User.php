<?php

namespace application\home\model;

use library\mysmarty\Model;

class User extends Model
{

    protected $database = 'test';
    protected $table = 'user';

    public function addStudentIdAttr($data)
    {
        return rand(10,20);
    }

    public function addMEmailAttr($data)
    {
        return $data['m_name'] . '@qq.com';
    }
}