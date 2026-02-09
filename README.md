<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/yii_logo_light.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/yii_logo_dark.svg">
        <img src="https://www.yiiframework.com/image/yii_logo_dark.svg" alt="Yii Framework" height="100px">
    </picture>
    <h1 align="center">Debug Extension for Yii 2</h1>
    <br>
</p>

This extension provides a debugger for [Yii framework 2.0](https://www.yiiframework.com) applications. When this extension is used,
a debugger toolbar will appear at the bottom of every page. The extension also provides
a set of standalone pages to display more detailed debug information.

For license information check the [LICENSE](LICENSE.md)-file.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-debug.svg?style=for-the-badge&label=Stable&logo=packagist)](https://packagist.org/packages/yiisoft/yii2-debug)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-debug.svg?style=for-the-badge&label=Downloads)](https://packagist.org/packages/yiisoft/yii2-debug)
[![build](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-debug/build.yml?style=for-the-badge&logo=github&label=Build)](https://github.com/yiisoft/yii2-debug/actions?query=workflow%3Abuild)
[![codecov](https://img.shields.io/codecov/c/github/yiisoft/yii2-debug.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Codecov)](https://codecov.io/gh/yiisoft/yii2-debug)
[![Static Analysis](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-debug/static.yml?style=for-the-badge&label=Static)](https://github.com/yiisoft/yii2-debug/actions/workflows/static.yml)


Installation
------------

> [!IMPORTANT]
> - The minimum required [PHP](https://www.php.net/) version is PHP `7.4`.
> - It works best with PHP `8`.

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

or add

```
"yiisoft/yii2-debug": "~2.1.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            // uncomment and adjust the following to add your IP if you are not connecting from localhost.
            //'allowedIPs' => ['127.0.0.1', '::1'],
        ],
        // ...
    ],
    ...
];
```

You will see a debugger toolbar showing at the bottom of every page of your application.
You can click on the toolbar to see more detailed debug information.


Open Files in IDE
-----

You can create a link to open files in your favorite IDE with this configuration:

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'traceLine' => '<a href="phpstorm://open?url={file}&line={line}">{file}:{line}</a>',
            // uncomment and adjust the following to add your IP if you are not connecting from localhost.
            //'allowedIPs' => ['127.0.0.1', '::1'],
        ],
        // ...
    ],
    ...
];
```

You must make some changes to your OS. See these examples: 
 - PHPStorm: https://github.com/aik099/PhpStormProtocol
 - Sublime Text 3 on Windows or Linux: https://packagecontrol.io/packages/subl%20protocol
 - Sublime Text 3 on Mac: https://github.com/inopinatus/sublime_url

#### Virtualized or dockerized

If your application is run under a virtualized or dockerized environment, it is often the case that the application's 
base path is different inside of the virtual machine or container than on your host machine. For the links work in those
 situations, you can configure `tracePathMappings` like this (change the path to your app):

```php
'tracePathMappings' => [
    '/app' => '/path/to/your/app',
],
```

Or you can create a callback for `traceLine` for even more control:

```php
'traceLine' => function($options, $panel) {
    $filePath = $options['file'];
    if (StringHelper::startsWith($filePath, Yii::$app->basePath)) {
        $filePath = '/path/to/your/app' . substr($filePath, strlen(Yii::$app->basePath));
    }
    return strtr('<a href="ide://open?url=file://{file}&line={line}">{text}</a>', ['{file}' => $filePath]);
},
```

## Documentation

- [Internals](docs/internals.md)

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?style=for-the-badge&logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=for-the-badge&logo=yii)](https://www.yiiframework.com/)
[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=for-the-badge&logo=telegram)](https://t.me/yii2en)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=for-the-badge&logo=slack)](https://yiiframework.com/go/slack)
