<?php

namespace application\home\controller;

use library\mysmarty\Ckeditor;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $s = htmlspecialchars('<strong>2222</strong>');
        $html = <<<'HTML'
<pre>
<strong>2222</strong>
<a>ddddddddddd</a>
<p>ddddd

dddddd
</p>
</pre>
fsdfsfsdfsfsf
<pre>
<code>
<strong>2222</strong>
<a>ddddddddddd</a>
<p>ddddd

dddddd
</p>
<?php

echo 'hello world';
?>
<script>
let a = 1111;
console.log(a);
</script>
</code>

</pre>
HTML;

        $html .= '<pre>' . $s . '</pre>';
        var_dump($html);
        echo  PHP_EOL;
        echo '排版后';
        echo  PHP_EOL;
        $html = paiban($html);
        var_dump($html);
        echo  PHP_EOL;

        $this->assign('html', $html);
        $this->display();
    }
}