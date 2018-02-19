<?php

use Psr\Log\LogLevel;
use yii\log\Target;

/* @var $panel yii\debug\panels\LogPanel */
/* @var $data array */

$titles = ['all' => Yii::$app->i18n->format('Logged {n,plural,=1{1 message} other{# messages}}', ['n' => count($data['messages'])], 'en-US')];
$errorCount = count(Target::filterMessages($data['messages'], [LogLevel::ERROR]));
$warningCount = count(Target::filterMessages($data['messages'], [LogLevel::WARNING]));

if ($errorCount) {
    $titles['errors'] = Yii::$app->i18n->format('{n,plural,=1{1 error} other{# errors}}', ['n' => $errorCount], 'en-US');
}

if ($warningCount) {
    $titles['warnings'] = Yii::$app->i18n->format('{n,plural,=1{1 warning} other{# warnings}}', ['n' => $warningCount], 'en-US');
}
?>

<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="<?= implode(',&nbsp;', $titles) ?>">Log
        <span class="yii-debug-toolbar__label"><?= count($data['messages']) ?></span>
    </a>
    <?php if ($errorCount): ?>
    <a href="<?= $panel->getUrl(['Log[level]' => LogLevel::ERROR])?>" title="<?= $titles['errors'] ?>">
        <span class="yii-debug-toolbar__label yii-debug-toolbar__label_important"><?= $errorCount ?></span>
    </a>
    <?php endif; ?>
    <?php if ($warningCount): ?>
    <a href="<?= $panel->getUrl(['Log[level]' => LogLevel::WARNING])?>" title="<?= $titles['warnings'] ?>">
        <span class="yii-debug-toolbar__label yii-debug-toolbar__label_warning"><?= $warningCount ?></span>
    </a>
    <?php endif; ?>
</div>
