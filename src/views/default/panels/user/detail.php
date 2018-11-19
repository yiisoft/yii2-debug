<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this \yii\web\View */
/* @var $panel yii\debug\panels\UserPanel */
?>

<h1>User</h1>

<?php
if (isset($panel->data['identity'])) {
    $items = [
        'nav' => ['User'],
        'content' => [
            '<h2>User Info</h2>' . DetailView::widget([
                'model' => $panel->data['identity'],
                'attributes' => $panel->data['attributes']
            ])
        ]
    ];
    if ($panel->data['rolesProvider'] || $panel->data['permissionsProvider']) {
        $items['nav'][] = 'Roles and Permissions';
        $item['content'][] = $this->render('roles', ['panel' => $panel]);
    }

    if ($panel->canSwitchUser()) {
        $items['nav'][] = 'Switch User';
        $item['content'][] = $this->render('switch', ['panel' => $panel]);
    }

    ?>
    <ul class="nav nav-tabs">
        <?php
        foreach ($items['nav'] as $k => $item) {
            Html::tag(
                'li',
                Html::a($item, '#u-tab-' . $k, [
                    'class' => $k === 0 ? 'nav-link active' : 'nav-link',
                    'data-toggle' => 'tab',
                    'role' => 'tab',
                    'aria-controls' => 'u-tab-' . $k,
                    'aria-selected' => $k === 0 ? 'true' : 'false'
                ]),
                [
                    'class' => 'nav-item'
                ]
            );
        }
        ?>
    </ul>
    <div class="tab-content">
        <?php
        foreach ($items['content'] as $k => $item) {
            Html::tag('div', $item, [
                'class' => $k === 0 ? 'tab-pane active' : 'tab-pane',
                'id' => 'u-tab-' . $k
            ]);
        }
        ?>
    </div>
    <?php

} else {
    echo 'Is guest.';
} ?>
