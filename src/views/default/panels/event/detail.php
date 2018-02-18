<?php

use yii\grid\GridView;

/* @var $panel yii\debug\panels\EventPanel */
/* @var $searchModel yii\debug\models\search\Event */
/* @var $dataProvider yii\data\ArrayDataProvider */

$yiiVersion = Yii::getVersion();
?>
<h1>Events</h1>

<?php if (!version_compare(Yii::getVersion(), '2.0.14', '>=') && strpos($yiiVersion, '-dev') === false) : ?>
    <div class="alert alert-danger">Yii Framework version >= 2.0.14 required for event panel to function</div>
<?php endif ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'log-panel-detailed-event',
    'options' => ['class' => 'detail-grid-view table-responsive'],
    'filterModel' => $searchModel,
    'filterUrl' => $panel->getUrl(),
    'columns' => [
        [
            'attribute' => 'time',
            'value' => function ($data) {
                $timeInSeconds = floor($data['time']);
                $millisecondsDiff = (int) (($data['time'] - (int) $timeInSeconds) * 1000);
                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'name',
            /*'headerOptions' => [
                'class' => 'sort-numerical'
            ],*/
        ],
        [
            'attribute' => 'class',
        ],
        [
            'header' => 'Sender',
            'attribute' => 'senderClass',
            'value' => function ($data) {
                return $data['senderClass'];
            },
        ],
        [
            'header' => 'Static',
            'attribute' => 'isStatic',
            'format' => 'boolean',
        ],
    ],
]); ?>
