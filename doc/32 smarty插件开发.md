插件开发参考教程：<https://www.smarty.net/docs/zh_CN/plugins.tpl>

插件存放目录为：`\mysmarty\extend\plugins`

**示例：带输出的函数插件**

将 `function.eightball.php` 放在插件目录 `\mysmarty\extend\plugins`

```php
<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * 文件名:     function.eightball.php
 * Type:     function
 * Name:     eightball
 * Purpose:  输出一个随机的答案
 * -------------------------------------------------------------
 */
function smarty_function_eightball($params, Smarty_Internal_Template $template)
{
    $answers = array('Yes',
                     'No',
                     'No way',
                     'Outlook not so good',
                     'Ask again soon',
                     'Maybe in your reality');

    $result = array_rand($answers);
    return $answers[$result];
}
```

模板使用

```html
Question: Will we ever have time travel?
Answer: {eightball}
```

