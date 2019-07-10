<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii2 generate swagger annotation</h1>
    <br>
</p>

This extension provides a Web-based code generator, called Gii, for [Yii framework 2.0](http://www.yiiframework.com) applications.
You can use Gii to quickly generate `swagger 2.0(openapi 2.0)` annotation.

For license information check the [LICENSE](LICENSE.md)-file.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require lengbin/yii-gii-swagger
```

or add

```
"lengbin/yii-gii-swagger": *
```

to the require-dev section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration at your `application\config\main_local.php` as follows:

```php
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'generators' => [
            'swagger' => [
                'class'      => 'lengbin\gii\swagger\Generator', //class
                'parameters' => [],                              //set default request parameters
                'responses'  => [                               //set default respons parameters
                    'responseStatus'      => [200, 'default'],
                    'responseDescription' => ['success', '请求失败， http status 强行转为200, 通过code判断'],
                    'ref'                 => ['SuccessDefault', 'ErrorDefault'],
                ],
            ],
            //...
        ],
    ];
    //...
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/gii
```

extension
----

You can use swagger doc  extension [yii-swagger](https://github.com/ice-leng/yii-swagger).


Screenshots
-----------
Entire page
![giiant-0 2-screen-1](./image/1.png)
Custom parameter
![giiant-0 2-screen-2](./image/2.png)
generate `swagger 2.0(openapi 2.0)` annotation
![giiant-0 2-screen-3](./image/3.jpg)

