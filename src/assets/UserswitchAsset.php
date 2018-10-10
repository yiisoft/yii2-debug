<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\assets;

use yii\web\AssetBundle;

/**
 * Userswitch asset bundle
 *
 * @author Semen Dubina <yii2debug@sam002.net>
 * @since 2.0.10
 */
class UserswitchAsset extends AssetBundle
{
    public $sourcePath = '@yii/debug/resource';
    public $js = [
        'userswitch.js',
    ];
}