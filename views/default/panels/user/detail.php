<?php

/* @var $panel yii\debug\panels\UserPanel */

use yii\grid\GridView;
use yii\widgets\DetailView;

?>

<h1>User Info</h1>


<?php if (!Yii::$app->user->isGuest) {

    echo DetailView::widget([
        'model' => $panel->data['identity'],
        'attributes' => $panel->data['attributes']
    ]);


    if ($panel->data['rolesProvider']) {
        echo '<h2>Roles</h2>';

        echo GridView::widget([
            'dataProvider' => $panel->data['rolesProvider'],
            'columns' => [
                'name',
                'description',
                'ruleName',
                'data',
                'createdAt:datetime',
                'updatedAt:datetime'
            ]
        ]);
    }

    if ($panel->data['permissionsProvider']) {
        echo '<h2>Permissions</h2>';

        echo GridView::widget([
            'dataProvider' => $panel->data['permissionsProvider'],
            'columns' => [
                'name',
                'description',
                'ruleName',
                'data',
                'createdAt:datetime',
                'updatedAt:datetime'
            ]
        ]);
    }

} ?>

