<?php

use library\mysmarty\Emoji;

/**
 * Date: 2019/5/21
 * Time: 15:15
 */
function smarty_modifier_emoji($str)
{
   Emoji::echoByName($str);
}