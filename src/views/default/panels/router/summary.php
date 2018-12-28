<?php

/* @var $panel yii\debug\panels\RouterPanel */

?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Action: <?= $panel->data['action'] ?>">Route <span
            class="yii-debug-toolbar__label"><?= $panel->data['route'] ?></span></a>
</div>
