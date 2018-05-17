<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\VarDumper;
use yii\log\Logger;

/* @var $panel yii\debug\panels\LogPanel */
/* @var $searchModel yii\debug\models\search\Log */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<h1>Log Messages</h1>
<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'log-panel-detailed-grid',
    'options' => ['class' => 'detail-grid-view table-responsive'],
    'filterModel' => $searchModel,
    'filterUrl' => $panel->getUrl(),
    'rowOptions' => function ($model) {
        switch ($model['level']) {
            case Logger::LEVEL_ERROR : return ['class' => 'danger'];
            case Logger::LEVEL_WARNING : return ['class' => 'warning'];
            case Logger::LEVEL_INFO : return ['class' => 'success'];
            default: return [];
        }
    },
    'columns' => [
        [
            'attribute' => 'time',
            'value' => function ($data) {
                $timeInSeconds = $data['time'] / 1000;
                $millisecondsDiff = (int) (($timeInSeconds - (int) $timeInSeconds) * 1000);

                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'time_since_previous',
            'value' => function ($data) {
                $previousDateTime = \DateTime::createFromFormat('U.u', $data['time_of_previous'] / 1000);
                $thisDateTime = \DateTime::createFromFormat('U.u', $data['time'] / 1000);

                $diffInSeconds = ($data['time'] - $data['time_of_previous']) / 1000;
                $diffInMs = (int) (($diffInSeconds - (int) $diffInSeconds) * 1000);

                $diff = $thisDateTime->diff($previousDateTime);
                $diffHours = (int) $diff->format('%h');
                $diffMinutes = (int) $diff->format('%i');
                $diffSeconds = (int) $diff->format('%s');

                $formattedDiff = [];
                if ($diffHours > 0) {
                    $formattedDiff[] = $diffHours . 'h';
                }
                if ($diffMinutes > 0) {
                    $formattedDiff[] = $diffMinutes . 'm';
                }
                if ($diffSeconds > 0) {
                    $formattedDiff[] = $diffSeconds . 's';
                }
                $formattedDiff[] = $diffInMs . 'ms';
                $formattedDiff = implode('&nbsp;', $formattedDiff);

                return $formattedDiff;
            },
            'format' => 'raw',
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'level',
            'value' => function ($data) {
                return Logger::getLevelName($data['level']);
            },
            'filter' => [
                Logger::LEVEL_TRACE => ' Trace ',
                Logger::LEVEL_INFO => ' Info ',
                Logger::LEVEL_WARNING => ' Warning ',
                Logger::LEVEL_ERROR => ' Error ',
            ],
        ],
        'category',
        [
            'attribute' => 'message',
            'value' => function ($data) use ($panel) {
                $message = Html::encode(is_string($data['message']) ? $data['message'] : VarDumper::export($data['message']));
                if (!empty($data['trace'])) {
                    $message .= Html::ul($data['trace'], [
                        'class' => 'trace',
                        'item' => function ($trace) use ($panel) {
                            return '<li>' . $panel->getTraceLine($trace) . '</li>';
                        }
                    ]);
                }
                return $message;
            },
            'format' => 'raw',
            'options' => [
                'width' => '50%',
            ],
        ],
    ],
]);
