<?php

namespace yiiunit\debug\router\module\controllers;

use yii\web\Controller;

class ModuleWebController extends Controller
{
    public function actionInside()
    {
        return true;
    }
}
