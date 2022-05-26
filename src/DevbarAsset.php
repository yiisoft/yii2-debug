<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\web\AssetBundle;

/**
 * Debugger asset bundle
 *
 * @author Nikolay Kostyurin <jilizart@gmail.com>
 * @since 2.0
 */
class DevbarAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $baseUrl = 'https://unpkg.com/@devbar/toolbar/dist/';
    /**
     * {@inheritdoc}
     */
    public $js = [
        YII_DEBUG ? 'dev-bar.js' : 'dev-bar.min.js'
    ];
}
