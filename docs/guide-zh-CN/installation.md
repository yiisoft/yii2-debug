安装
============

## 获取 Composer 安装包

安装此扩展的首选方式是通过 [composer](http://getcomposer.org/download/)。

可以运行

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

或者添加

```
"yiisoft/yii2-debug": "~2.0.0"
```

在 `composer.json` 文件中的必要部分。


## 配置应用程序

启用扩展，将以下代码添加到您的配置文件中，以启用调试模块：

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
    ],
]
```

默认情况下，调试模块仅工作在从本地主机浏览网页。如果你想在远程（演示）
服务器上使用它，添加参数 `allowedIPs` 来配置您的 IP 白名单：

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['1.2.3.4', '127.0.0.1', '::1']
    ]
]
```

如果你使用的是 `enableStrictParsing` URL 管理选项，在您的 `rules` 中添加以下代码：

```php
'urlManager' => [
    'enableStrictParsing' => true,
    'rules' => [
        // ...
        'debug/<controller>/<action>' => 'debug/<controller>/<action>',
    ],
],
```

> 注意：调试器在 `@runtime/debug` 目录中存储每个请求的信息。如果您在使用调试器
> 的时候出现问题，例如使用中出现奇怪的错误信息，或工具栏上没有显示任何请求，检查
> WEB 服务器是否具有足够的权限访问该目录和内部的文件。


### 日志和分析附加配置

日志和分析都是简单而强大的工具，可以帮助你理解框架和应用程序
的执行流程。这些工具对于开发和生产环境都是有用的。

在生产环境中，您应该手动记录重要的信息，如
[日志指南部分](https://github.com/yiisoft/yii2/blob/master/docs/guide/runtime-logging.md) 中所描述的。为了持续记录生产环境中所有的日志信息牺牲了太多的性能。

在开发环境中，日志越多越好，记录执行跟踪是非常有用的。

为了查看跟踪信息，这将有助于你理解框架幕后发生了什么，您
需要在配置文件中设置跟踪级别：

```php
return [
    // ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0, // <-- 这里
```

默认情况下，如果 Yii 是在调试模式下运行，跟踪级别自动设置为 `3`，由你的 `index.php` 文件中
以下行的存在而决定：

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> 注意：确保在生产环境中禁用调试模式，因为它可能有显著和不利的性能效果。
此外调试模式可能会暴露敏感信息给终端用户。
