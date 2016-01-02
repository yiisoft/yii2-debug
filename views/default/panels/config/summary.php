<?php
/* @var $panel yii\debug\panels\ConfigPanel */
?>
<div class="yii-debug-toolbar-block config">
    <a href="<?= $panel->getUrl() ?>">
        <span class="label"><?= $panel->data['application']['yii'] ?></span>
        PHP
        <span class="label"><?= $panel->data['php']['version'] ?></span>
    </a>
</div>
