<?php

namespace yiiunit\debug\router\controllers;

use yii\web\Controller;

class RedirectController extends Controller
{
    public function init()
    {
        \Yii::$app->response->redirect('web/first');
    }

    public function actionOnly()
    {
        return true;
    }

    public function actions()
    {
        return ['test' => 'yii\web\ErrorAction'];
    }
}
