<?php
/* @var $panel yii\debug\panels\DbPanel */
/* @var $queryCount int */
/* @var $queryTime int */
/* @var $excessiveCallerCount int */

$title = "Executed $queryCount database queries which took $queryTime.";
$warning = '';

if ($panel->isQueryCountCritical($queryCount)) {
    $warning .= "Too many queries, allowed count is {$panel->criticalQueryThreshold}.";
}
if ($excessiveCallerCount) {
    $warning .= ($warning ? ' &#10;' : '') . $excessiveCallerCount . ' '
        . ($excessiveCallerCount == 1 ? 'caller is' : 'callers are')
        . ' making too many calls.';
}
?>
<?php if ($queryCount): ?>
    <div class="yii-debug-toolbar__block">
        <a href="<?= $panel->getUrl() ?>" title="<?= $title ?>">
            <?= $panel->getSummaryName() ?>
            <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= $queryCount ?></span>
            <?php if ($warning): ?>
                <span title="<?= $warning ?>">&#x26a0;</span>
            <?php endif; ?>
            <span class="yii-debug-toolbar__label"><?= $queryTime ?></span>
        </a>
    </div>
<?php endif; ?>
