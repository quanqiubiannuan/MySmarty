官网：<https://ckeditor.com/ckeditor-5/>

**安装ckeditor**

```html
<html>
<head>
    <style type="text/css">
        .ck-editor__editable {
            min-height: 300px !important;
        }
    </style>
</head>
<body>

<form action="{controller}/post" method="post" enctype="multipart/form-data">
    <textarea id="content" name="content" class="form-control"></textarea>
    <br>
    <button type="submit">提交</button>
</form>


<script src="{url}/static/js/ckeditor.js"></script>
<script src="{url}/static/js/zh-cn.js"></script>
<script src="{url}/static/js/image-upload.js"></script>
<script>
    let imageUploadUrl = '{url}/home/index/upload';
    ClassicEditor
        .create(document.querySelector('#content'), {
            language: 'zh-cn',
            extraPlugins: [ MyCustomUploadAdapterPlugin ]
        })
        .then(editor => {

        })
        .catch(error => {
            console.error(error);
        });
</script>

</body>
</html>
```

`ckeditor.js` 编辑器js文件

`zh-cn.js` 中文语言包文件

`image-upload.js` 自定义图片上传类

```javascript
class MyUploadAdapter {
    constructor(loader) {
        // 构造方法
        this.loader = loader;
    }

    // 开始上传
    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                this._initRequest();
                this._initListeners(resolve, reject, file);
                this._sendRequest(file);
            }));
    }

    // 停止上传
    abort() {
        if (this.xhr) {
            this.xhr.abort();
        }
    }

    // 初始化
    _initRequest() {
        const xhr = this.xhr = new XMLHttpRequest();
        xhr.open('POST', imageUploadUrl, true);
        xhr.responseType = 'json';
    }

    // 初始化监听
    _initListeners(resolve, reject, file) {
        const xhr = this.xhr;
        const loader = this.loader;
        const genericErrorText = `图片上传失败：${file.name}.`;

        xhr.addEventListener('error', () => reject(genericErrorText));
        xhr.addEventListener('abort', () => reject());
        xhr.addEventListener('load', () => {
            const response = xhr.response;
            if (!response || response.error) {
                return reject(response && response.error ? response.error.message : genericErrorText);
            }
            resolve({
                default: response.url
            });
        });
        if (xhr.upload) {
            xhr.upload.addEventListener('progress', evt => {
                if (evt.lengthComputable) {
                    loader.uploadTotal = evt.total;
                    loader.uploaded = evt.loaded;
                }
            });
        }
    }

    // 请求
    _sendRequest(file) {
        const data = new FormData();
        data.append('upload', file);
        this.xhr.send(data);
    }
}

function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}
```

**控制器代码**

```php
<?php

namespace application\home\controller;

use library\mysmarty\Upload;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $this->display();
    }


    /**
     * 图片上传
     * @throws \Exception
     */
    public function upload()
    {
        $pic = Upload::getInstance()
            ->setLimitType(['image/png', 'image/jpeg', 'image/gif', 'image/jpg'])
            ->setLimitSize(1048576)
            ->move('upload');
        if (empty($pic)) {
            http_response_code(500);
            exit();
        }
        echo json_encode(['default' => $pic, 'url' => $pic]);
        exit();
    }

    public function post(){
        var_dump($_POST);
    }
}
```

对编辑器的内容进行安全接收

```php
public function post(){
 	$content = Ckeditor::getInstance()->getContent($_POST['content']);
 	var_dump($content);
 	var_dump(paiban($_POST['content']));
}
```

