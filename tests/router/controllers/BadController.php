<?php

namespace yiiunit\debug\router\controllers;

use yii\web\Controller;

class BadController extends Controller
{
    public function init()
    {
        throw new \Exception('Simulates problem with controller when initialing');
    }

    public function actionOnly()
    {
        return true;
    }

    public function actions()
    {
        return ['test' => 'Something not important'];
    }
}
