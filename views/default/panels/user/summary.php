<?php
/* @var $panel yii\debug\panels\UserPanel */
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>">
        <?php if (Yii::$app->user->isGuest): ?>
            <span class="yii-debug-toolbar__label">Guest</span>
        <?php elseif($panel->userSwitch->isMainUser()): ?>
            User <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= Yii::$app->user->id ?></span>
        <?php else: ?>
            User switching <span class="yii-debug-toolbar__label yii-debug-toolbar__label_warning"><?= Yii::$app->user->id ?></span>
        <?php endif; ?>
    </a>
</div>
