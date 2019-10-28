<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$root = dirname(__DIR__, 3);

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';

$config = [
    'bootstrap' => ['debug'],
    'id' => 'yii2-webapp',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@vendor' => $root . '/vendor',
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'cookieValidationKey',
        ],
    ],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'allowedIPs' => ['*'],
        ],
    ],
    'container' => [
        'definitions' => [
            'yii\debug\DebugAsset' => ['sourcePath' => $root . '/src/assets'],
            'yii\debug\TimelineAsset' => ['sourcePath' => $root . '/src/assets'],
        ],
    ],
];

$application = new yii\web\Application($config);
$application->run();
