<?php
/* @var $panel yii\debug\panels\TimelinePanel */
/* @var $searchModel \yii\debug\models\search\Timeline */
/* @var $dataProvider \yii\data\ArrayDataProvider */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\debug\TimelineAsset;

TimelineAsset::register($this);
?>
<h1 class="debug-timeline-panel__title">Tilmeline - <?=number_format($panel->getDuration());?> ms</h1>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'action'=>$panel->getUrl(),
    'id' => 'debug-timeline-search',
    'enableClientScript' => false,
    'options' => ['class' => 'debug-timeline-panel__search']
]); ?>
<div class="duration">
    <?= Html::activeLabel($searchModel, 'duration') ?>
    <?= Html::activeInput('number', $searchModel, 'duration', ['min' => 0, 'size' => '3']); ?>
    <span> ms</span>
</div>
<div class="category">
    <?= Html::activeLabel($searchModel, 'category') ?>
    <?= Html::activeTextInput($searchModel, 'category'); ?>
</div>
<?php ActiveForm::end(); ?>
<div class="debug-timeline-panel">
    <div class="debug-timeline-panel__header">
        <?php foreach ($panel->getRulers() as $ms => $left): ?>
            <span class="ruler" style="margin-left: <?= $left ?>%"><b><?= sprintf('%.1f ms', $ms) ?></b></span>
        <?php endforeach; ?>
    </div>
    <div class="debug-timeline-panel__items">
        <?php Pjax::begin(['formSelector' => '#debug-timeline-search', 'linkSelector' => false, 'options' => ['id' => 'debug-timeline-panel__pjax']]); ?>
        <?php foreach ($dataProvider->models as $key => $model): ?>
            <?php $attr = $panel->getHtmlAttribute($model, $key); ?>
            <div class="debug-timeline-panel__item">
                <?= Html::tag('a', '<span class="category">' . $attr['title'] . '</span>', $attr) ?>
            </div>
        <?php endforeach; ?>
        <?php Pjax::end(); ?>
    </div>
</div>