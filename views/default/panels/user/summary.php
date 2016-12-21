<?php
/* @var $panel yii\debug\panels\UserPanel */
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">
        <?php if(Yii::$app->user->isGuest): ?>
            <span class="yii-debug-toolbar__label">Guest</span>
        <?php else: ?>
            <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info">User <?= Yii::$app->user->id?></span>
        <?php endif;?>
    </a>
</div>
