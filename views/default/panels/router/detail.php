<?php
/* @var $model  yii\debug\models\Router */
/* @var $logs [] */
/* @var $metric integer */


use \yii\helpers\Html;

echo '<h1>Routing - <small>' . Yii::t('yii', 'Tested {n} {n, plural, =1{rule} other{rules}} {m, plural, =0{} other{before match}}', ['n' => $model->metric, 'm' => (int)$model->hasMatch]) . '</small></h1>';
?>
<?php if ($model->message !== null): ?>
    <div class="alert alert-info" role="alert">
        <?= Html::encode($model->message); ?>
    </div>
<?php endif; ?>

<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>#</th>
        <th>Rule</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->logs as $i => $log): ?>
        <tr<?= $log['match'] ? ' class="success"' : '' ?>>
            <td><?= $i + 1; ?></td>
            <td><?= Html::encode($log['rule']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>