<?php
/* @var $panel yii\debug\panels\DbPanel */
/* @var $queryCount integer */
/* @var $queryTime integer */
?>
<a href="<?= $panel->getUrl() ?>" title="Executed <?= $queryCount ?> database queries which took <?= $queryTime ?>.">
    <?= $panel->getSummaryName() ?> <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= $queryCount ?></span> <span class="yii-debug-toolbar__label"><?= $queryTime ?></span>
</a>
