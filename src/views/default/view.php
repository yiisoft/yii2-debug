<?php

use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $summary array */
/* @var $tag string */
/* @var $manifest array */
/* @var $panels \yii\debug\Panel[] */
/* @var $activePanel \yii\debug\Panel */

$this->title = 'Yii Debugger';
?>
<div class="default-view">
    <div id="yii-debug-toolbar" class="yii-debug-toolbar yii-debug-toolbar_position_top" style="display: none;">
        <div class="yii-debug-toolbar__bar">
            <div class="yii-debug-toolbar__block yii-debug-toolbar__title">
                <a href="<?= Url::to(['index']) ?>">
                    <img width="29" height="30" alt="" src="<?= \yii\debug\Module::getYiiLogo() ?>">
                </a>
            </div>

            <?php foreach ($panels as $panel): ?>
                <?= $panel->getSummary() ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container main-container yii-debug-main-container">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <?php
                    $options = [
                        'class' => ['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center']
                    ];
                    foreach ($panels as $id => $panel) {
                        $label = Html::tag('span', Html::encode($panel->getName())) . '<span class="icon">&rang;</span>';
                        echo Html::a($label, ['view', 'tag' => $tag, 'panel' => $id], [
                            'class' => $panel === $activePanel ? array_merge($options, ['active']) : $options,
                        ]);
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-10">
                <?php
                $statusCode = $summary['statusCode'];
                if ($statusCode === null) {
                    $statusCode = 200;
                }
                if ($statusCode >= 200 && $statusCode < 300) {
                    $calloutClass = 'callout-success';
                } elseif ($statusCode >= 300 && $statusCode < 400) {
                    $calloutClass = 'callout-info';
                } else {
                    $calloutClass = 'callout-important';
                }
                ?>
                <div class="callout <?= $calloutClass ?>">
                    <?php
                    $count = 0;
                    $items = [];
                    foreach ($manifest as $meta) {
                        $label = ($meta['tag'] == $tag ? Html::tag('strong',
                                '&#9654;&nbsp;' . $meta['tag']) : $meta['tag'])
                            . ': ' . $meta['method'] . ' ' . $meta['url'] . ($meta['ajax'] ? ' (AJAX)' : '')
                            . ', ' . date('Y-m-d h:i:s a', $meta['time'])
                            . ', ' . $meta['ip'];
                        $url = ['view', 'tag' => $meta['tag'], 'panel' => $activePanel->id];
                        $items[] = [
                            'label' => $label,
                            'url' => $url,
                        ];
                        if (++$count >= 10) {
                            break;
                        }
                    }

                    ?>
                    <div class="btn-group btn-group-sm" role="group">
                        <?=Html::a('All', ['index'], ['class' => ['btn', 'btn-outline-secondary']]);?>
                        <?=Html::a('Latest', ['view', 'panel' => $activePanel->id], ['class' => ['btn', 'btn-outline-secondary']]);?>
                        <div class="btn-group btn-group-sm" role="group">
                            <?=Html::button('Last 10', [
                                'type' => 'button',
                                'class' => ['btn', 'btn-outline-secondary', 'dropdown-toggle'],
                                'data' => [
                                    'toggle' => 'dropdown'
                                ],
                                'aria-haspopup' => 'true',
                                'aria-expanded' => 'false'
                            ]);?>
                            <?=\yii\widgets\Menu::widget([
                                'encodeLabels' => false,
                                'items' => $items,
                                'options' => ['class' => 'dropdown-menu'],
                                'itemOptions' => ['class' => 'dropdown-item']
                            ]);?>
                        </div>
                    </div>
                    <?php
                    echo "\n" . $summary['tag'] . ': ' . $summary['method'] . ' ' . Html::a(Html::encode($summary['url']),
                            $summary['url']);
                    echo ' at ' . date('Y-m-d h:i:s a', $summary['time']) . ' by ' . $summary['ip'];
                    ?>
                </div>
                <?= $activePanel->getDetail(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    if (!window.frameElement) {
        document.querySelector('#yii-debug-toolbar').style.display = 'block';
    }
</script>
