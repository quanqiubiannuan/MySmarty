<?php


namespace application\home\controller;


#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route
{
    public function __construct($url)
    {
        var_dump($url);
    }
}