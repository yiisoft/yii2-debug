<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\debug\support;

use yii\web\Controller;

class TestController extends Controller
{
    public function actionIndex(): string
    {
        return 'index';
    }
}
