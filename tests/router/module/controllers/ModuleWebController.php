<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\debug\router\module\controllers;

use yii\web\Controller;

class ModuleWebController extends Controller
{
    public function actionInside()
    {
        return true;
    }
}
