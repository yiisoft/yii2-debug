<?php
/* @var $model  yii\debug\models\Router */

use \yii\helpers\Html;

echo '<h1>Routing<small>' . Yii::t('yii', '{n, plural, =0{} =1{ - Tested {n} rule} other{ - Tested {n} rules}} {m, plural, =0{} other{before match}}', ['n' => $model->count, 'm' => (int)$model->hasMatch]) . '</small></h1>';
?>
<?php if ($model->message !== null): ?>
    <div class="alert alert-info">
        <?= Html::encode($model->message); ?>
    </div>
<?php endif; ?>
<?php if ($model->logs !== []): ?>
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
<?php endif; ?>