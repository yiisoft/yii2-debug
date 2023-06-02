<?php

use yii\debug\DbAsset;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $panel yii\debug\panels\DbPanel */
/* @var $searchModel yii\debug\models\search\Db */
/* @var $callerDataProvider yii\data\ArrayDataProvider */
/* @var $hasExplain bool */
/* @var $sumDuplicates int */
/* @var $this View */

echo Html::tag('h3', $panel->getName() . ' Callers');

echo GridView::widget([
    'dataProvider' => $callerDataProvider,
    'id' => 'db-panel-detailed-callers-grid',
    'options' => ['class' => 'detail-grid-view db-panel-detailed-grid table-responsive'],
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
            'label' => 'Caller',
            'attribute' => 'trace',
            'value' => function ($data) use ($hasExplain, $panel) {
                return Html::ul($data['trace'], [
                    'class' => 'trace',
                    'item' => function ($trace) use ($panel) {
                        return '<li>' . $panel->getTraceLine($trace) . '</li>';
                    },
                ]);
            },
            'format' => 'raw',
            'options' => [
                'width' => '25%',
            ],
        ],
        [
            'label' => 'No. of Calls',
            'attribute' => 'numCalls',
            'value' => function ($data) use ($panel) {
                $result = $data['numCalls'];
                if ($panel->isNumberOfCallsExcessive($data['numCalls'])) {
                    $result .= ' ' . Html::tag('span', '&#x26a0;', [
                        'title' => 'Too many calls, number of calls should stay below ' . $panel->excessiveCallerThreshold,
                    ]);
                }
                return $result;
            },
            'format' => 'raw',
            'options' => [
                'width' => '5%',
            ],
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'totalDuration',
            'value' => function ($data) {
                return sprintf('%.1f ms', $data['totalDuration']);
            },
            'options' => [
                'width' => '10%',
            ],
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'queries',
            'value' => function ($data) use ($hasExplain, $panel) {
                $queries =
                    '<table style="width: 100%;">
                        <thead>
                            <th style="width: 5%;">Time</th>
                            <th style="width: 5%;">Duration</th>
                            <th>Queries</th>
                        </thead>
                        <tbody>';

                foreach ($data['queries'] as $queryData) {
                    $queries .= '<tr>';

                    $timeInSeconds = $queryData['timestamp'] / 1000;
                    $millisecondsDiff = (int)(($timeInSeconds - (int)$timeInSeconds) * 1000);
                    $queries .= '<td>' . date('H:i:s.', (int)$timeInSeconds)
                        . sprintf('%03d', $millisecondsDiff) . '</td>';

                    $queries .= '<td>' . sprintf('%.1f ms', $queryData['duration']) . '</td>';

                    $queries .= '<td>' . Html::tag('div', Html::encode($queryData['query']));
                    if ($hasExplain && $panel::canBeExplained($queryData['type'])) {
                        $queries .= Html::tag('p', '', ['class' => 'db-explain-text']);

                        $queries .= Html::tag(
                            'div',
                            Html::a(
                                '[+] Explain',
                                ['db-explain', 'seq' => $queryData['seq'], 'tag' => Yii::$app->controller->summary['tag']]
                            ),
                            ['class' => 'db-explain']
                        );
                    }
                    $queries .= '</td>
                        </tr>';
                }

                $queries .= '
                        </tbody>
                    </table>';

                return $queries;
            },
            'format' => 'raw',
            'options' => [
                'width' => '60%',
            ],
        ]
    ],
]);

if ($hasExplain) {
    DbAsset::register($this);

    echo Html::tag(
        'div',
        Html::a('[+] Explain all', 'javascript:;'),
        ['class' => 'db-explain-all']
    );
}
