<?php

namespace application\home\controller;

use library\mysmarty\BrowserDownload;

class Index
{
    public function test()
    {
        BrowserDownload::getInstance()
            ->setData(file_get_contents('https://www.wyzda.com/static/v20200328/images/logo.png'))
            ->setMimeType('image/jpg')
            ->output('a.jpg');
    }
}