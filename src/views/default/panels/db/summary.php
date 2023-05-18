<?php
/* @var $panel yii\debug\panels\DbPanel */
/* @var $queryCount int */
/* @var $queryTime int */
/* @var $excessiveCallerCount int */

$title = "Executed $queryCount database queries which took $queryTime.";
$hasError = false;

if ($queryCount >= $panel->criticalQueryThreshold) {
    $title .= " &#10;Too many queries, allowed count is {$panel->criticalQueryThreshold}.";
    $hasError = true;
}
if ($excessiveCallerCount) {
    $title .= ' &#10;' . $excessiveCallerCount . ' '
        . ($excessiveCallerCount == 1 ? 'caller is' : 'callers are')
        .   ' making too many calls.';
    $hasError = true;
}
?>
<?php if ($queryCount): ?>
    <div class="yii-debug-toolbar__block">
        <a href="<?= $panel->getUrl() ?>"
           title="<?= $title ?>">
            <?= $panel->getSummaryName() ?>
                <span class="yii-debug-toolbar__label
                    <?= $hasError ? 'yii-debug-toolbar__label_error' : 'yii-debug-toolbar__label_info' ?>"
                ><?= $queryCount ?></span> <span
                class="yii-debug-toolbar__label"><?= $queryTime ?></span>
        </a>
    </div>
<?php endif; ?>
