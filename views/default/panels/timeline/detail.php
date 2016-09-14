<?php
/* @var $panel yii\debug\panels\TimelinePanel */
/* @var $searchModel \yii\debug\models\search\Timeline */
/* @var $dataProvider \yii\debug\components\TimelineDataProvider */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\debug\TimelineAsset;

TimelineAsset::register($this);
?>
<h1 class="debug-timeline-panel__title">Tilmeline - <?= number_format($dataProvider->duration); ?> ms</h1>

<?php $form = ActiveForm::begin(['method' => 'get', 'action' => $panel->getUrl(), 'id' => 'debug-timeline-search', 'enableClientScript' => false, 'options' => ['class' => 'debug-timeline-panel__search']]); ?>
<div class="duration">
    <?= Html::activeLabel($searchModel, 'duration') ?>
    <?= Html::activeInput('number', $searchModel, 'duration', ['min' => 0, 'size' => '3']); ?>
    <span>ms</span>
</div>
<div class="category">
    <?= Html::activeLabel($searchModel, 'category') ?>
    <?= Html::activeTextInput($searchModel, 'category'); ?>
</div>
<?php ActiveForm::end(); ?>
<div class="debug-timeline-panel">
    <div class="debug-timeline-panel__header">
        <?php foreach ($dataProvider->getRulers() as $ms => $left): ?>
            <span class="ruler" style="margin-left: <?= $left ?>%"><b><?= sprintf('%.1f ms', $ms) ?></b></span>
        <?php endforeach; ?>
    </div>
    <div class="debug-timeline-panel__items">
        <?php Pjax::begin(['formSelector' => '#debug-timeline-search', 'linkSelector' => false, 'options' => ['id' => 'debug-timeline-panel__pjax']]); ?>
        <?php if (($models = $dataProvider->models) === []): ?>
            <div class="debug-timeline-panel__item empty">
                <span><?= Yii::t('yii', 'No results found.'); ?></span>
            </div>
        <?php else: ?>
            <?php foreach ($models as $key => $model): ?>
                <div class="debug-timeline-panel__item">
                    <?php if ($model['child']): ?>
                        <span class="ruler ruler-start" style="height: <?= $model['child'] * 21; ?>px; margin-left: <?= $model['css']['left']; ?>%"></span>
                    <?php endif; ?>
                    <?= Html::tag('a', '<span class="category">' . Html::encode($model['category']) . ' <span>' . sprintf('%.1f ms', $model['duration']) . '</span></span>', ['tabindex'=>$key+1,'title' => $model['info'], 'class' => $dataProvider->getCssClass($model), 'style' => 'margin-left:' . $model['css']['left'] . '%;width:' . $model['css']['width'] . '%']); ?>
                    <?php if ($model['child']): ?>
                        <span class="ruler ruler-end" style="height: <?= $model['child'] * 21; ?>px; margin-left: <?= $model['css']['left'] + $model['css']['width'] . '%'; ?>"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php Pjax::end(); ?>
    </div>
</div>