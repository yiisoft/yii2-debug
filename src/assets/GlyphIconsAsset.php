<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\assets;

use yii\web\AssetBundle;

/**
 * Class GlyphiconsAsset
 * @package yii\debug\assets
 *
 * @see https://www.w3schools.com/bootstrap/bootstrap_ref_comp_glyphs.asp
 *
 * @author Alex Loban <lav451@gmail.com>
 * @since 2.1
 */
class GlyphIconsAsset extends AssetBundle
{
    public $sourcePath = '@bower/glyphicons-only-bootstrap';

    public $css = [
        'css/bootstrap.min.css',
    ];
}