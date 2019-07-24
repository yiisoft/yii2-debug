<?php
/* @var $panel yii\debug\panels\LogPanel */
/* @var $searchModel yii\debug\models\search\Log */
/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\log\Logger;

?>
    <h1>Log Messages</h1>
<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'log-panel-detailed-grid',
    'options' => ['class' => ['detail-grid-view', 'table-responsive', 'logs-messages-table']],
    'filterModel' => $searchModel,
    'filterUrl' => $panel->getUrl(),
    'rowOptions' => function ($model) {
        $options = [
            'id' => 'log-' . $model['id']
        ];
        switch ($model['level']) {
            case Logger::LEVEL_ERROR : Html::addCssClass($options, 'table-danger'); break;
            case Logger::LEVEL_WARNING : Html::addCssClass($options, 'table-warning'); break;
            case Logger::LEVEL_INFO : Html::addCssClass($options, 'table-success'); break;
        }
        return $options;
    },
    'pager' => [
        'linkContainerOptions' => [
            'class' => 'page-item'
        ],
        'linkOptions' => [
            'class' => 'page-link'
        ],
        'disabledListItemSubTagOptions' => [
            'tag' => 'a',
            'href' => 'javascript:;',
            'tabindex' => '-1',
            'class' => 'page-link'
        ]
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'label' => '#',
        ],
        [
            'attribute' => 'time',
            'value' => function ($data) {
                $timeInSeconds = $data['time'] / 1000;
                $millisecondsDiff = (int)(($timeInSeconds - (int)$timeInSeconds) * 1000);

                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'time_since_previous',
            'value' => function ($data) {
                $timeOfPrevious = $data['time_of_previous'] / 1000;
                if (strpos($timeOfPrevious, '.') === false) {
                    $timeOfPrevious = $timeOfPrevious . '.0';
                }
                $time = $data['time'] / 1000;
                if (strpos($time, '.') === false) {
                    $time = $time . '.0';
                }
                $previousDateTime = \DateTime::createFromFormat('U.u', $timeOfPrevious);
                $thisDateTime = \DateTime::createFromFormat('U.u', $time);

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

                $previousBtnOptions = [
                    'class' => 'btn btn-default',
                ];
                $nextBtnOptions = [
                    'class' => 'btn btn-default',
                ];
                if (is_null($data['id_of_previous'])) {
                    Html::addCssClass($previousBtnOptions, 'disabled');
                }
                if (is_null($data['id_of_next'])) {
                    Html::addCssClass($nextBtnOptions, 'disabled');
                }

                return
                    '<div class="btn-group btn-group-xs" role="group">' .
                    Html::a(
                        '<i class="glyphicon glyphicon-step-backward"></i>',
                        '#log-' . $data['id_of_previous'],
                        $previousBtnOptions
                    ) .
                    Html::a(
                        $formattedDiff,
                        '#log-' . $data['id'],
                        [
                            'class' => 'btn btn-default',
                        ]
                    ) .
                    Html::a(
                        '<i class="glyphicon glyphicon-step-forward"></i>',
                        '#log-' . $data['id_of_next'],
                        $nextBtnOptions
                    ) .
                    '</div>';
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
