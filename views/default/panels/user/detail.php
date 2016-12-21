<?php

/* @var $panel yii\debug\panels\UserPanel */

use yii\widgets\DetailView;

if (!Yii::$app->user->isGuest) {
    echo DetailView::widget([
        'model' => $panel->data['identity'],
        'attributes' => $panel->data['attributes']
    ]);
};

