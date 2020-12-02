**控制器**

```php
<?php

namespace application\home\controller;

use library\mysmarty\Emoji;
use library\mysmarty\Controller;

class Index extends Controller
{

    public function test()
    {
        //输出表情
        Emoji::echoByName('sunglasses');
        //获取所有表情名
        var_dump(Emoji::getAllName());
    }
}
```

```php
😎array(67) {
  [0]=>
  string(8) "birthday"
  [1]=>
  string(7) "running"
  [2]=>
  string(5) "house"
  [3]=>
  string(6) "office"
  [4]=>
  string(4) "shit"
  [5]=>
  string(8) "computer"
  [6]=>
  string(8) "moneybag"
  [7]=>
  string(6) "camera"
  [8]=>
  string(8) "thumbsup"
  [9]=>
  string(10) "thumbsdown"
  [10]=>
  string(6) "family"
  [11]=>
  string(4) "kiss"
  [12]=>
  string(3) "gun"
  [13]=>
  string(8) "grinning"
  [14]=>
  string(4) "grin"
  [15]=>
  string(3) "joy"
  [16]=>
  string(6) "smiley"
  [17]=>
  string(5) "smile"
  [18]=>
  string(11) "sweat_smile"
  [19]=>
  string(8) "laughing"
  [20]=>
  string(4) "wink"
  [21]=>
  string(5) "blush"
  [22]=>
  string(3) "yum"
  [23]=>
  string(8) "relieved"
  [24]=>
  string(10) "sunglasses"
  [25]=>
  string(10) "heart_eyes"
  [26]=>
  string(13) "kissing_heart"
  [27]=>
  string(7) "kissing"
  [28]=>
  string(20) "kissing_smiling_eyes"
  [29]=>
  string(19) "kissing_closed_eyes"
  [30]=>
  string(16) "stuck_out_tongue"
  [31]=>
  string(28) "stuck_out_tongue_closed_eyes"
  [32]=>
  string(12) "disappointed"
  [33]=>
  string(5) "angry"
  [34]=>
  string(4) "pout"
  [35]=>
  string(3) "cry"
  [36]=>
  string(9) "persevere"
  [37]=>
  string(7) "triumph"
  [38]=>
  string(20) "disappointed_relived"
  [39]=>
  string(7) "fearful"
  [40]=>
  string(5) "weary"
  [41]=>
  string(6) "sleepy"
  [42]=>
  string(5) "tired"
  [43]=>
  string(10) "loudly_cry"
  [44]=>
  string(10) "cold_sweat"
  [45]=>
  string(6) "scream"
  [46]=>
  string(10) "astonished"
  [47]=>
  string(7) "flushed"
  [48]=>
  string(10) "dizzy_face"
  [49]=>
  string(4) "mask"
  [50]=>
  string(12) "grinning_cat"
  [51]=>
  string(7) "joy_cat"
  [52]=>
  string(11) "smiling_cat"
  [53]=>
  string(7) "no_good"
  [54]=>
  string(8) "ok_woman"
  [55]=>
  string(3) "bow"
  [56]=>
  string(11) "see_no_evil"
  [57]=>
  string(12) "hear_no_evil"
  [58]=>
  string(13) "speak_no_evil"
  [59]=>
  string(12) "raising_hand"
  [60]=>
  string(12) "raised_hands"
  [61]=>
  string(15) "person_frowning"
  [62]=>
  string(4) "pray"
  [63]=>
  string(7) "worried"
  [64]=>
  string(7) "relaxed"
  [65]=>
  string(16) "white_check_mark"
  [66]=>
  string(8) "question"
}
```

**在模板文件中使用**

控制器

```php
<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{

    public function test()
    {
        $this->assign('sunglasses', 'sunglasses');
        $this->display();
    }
}
```

模板文件

```php
<html>
<head>
</head>

<body>
{$sunglasses|emoji}
</body>
</html>
```

浏览器查看源代码如下

```html
<html>
<head>
</head>

<body>
😎
</body>
</html>
```

