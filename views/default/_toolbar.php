<?php
/* @var $this \yii\web\View */
/* @var $panels \yii\debug\Panel[] */
/* @var $tag string */
/* @var $position string */

use yii\helpers\Url;

?>
<div class="yii-debug-toolbar-row">
    <div class="yii-debug-toolbar-block title">
        <a href="<?= Url::to(['index']) ?>">
            <img width="29" height="30" alt="" src="<?= \yii\debug\Module::getYiiLogo() ?>">
        </a>
    </div>

    <?php foreach ($panels as $panel): ?>
        <?= $panel->getSummary() ?>
    <?php endforeach; ?>
    
</div>
