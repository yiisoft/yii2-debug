<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

if (is_dir(__DIR__ . '/../vendor/')) {
    $vendorRoot = __DIR__ . '/../vendor'; //this extension has its own vendor folder
} else {
    $vendorRoot = __DIR__ . '/../../..'; //this extension is part of a project vendor folder
}
require_once($vendorRoot . '/autoload.php');
require_once($vendorRoot . '/yiisoft/yii2/Yii.php');

Yii::setAlias('@yiiunit/debug', __DIR__);
Yii::setAlias('@yii/debug', dirname(__DIR__) . '/src');

require_once(__DIR__ . '/compatibility.php');