<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\debug\router;

use yii\web\UrlRuleInterface;

class CustomRuleStub implements UrlRuleInterface
{
    public function parseRequest($manager, $request)
    {
        return false;
    }

    public function createUrl($manager, $route, $params)
    {
        return false;
    }
}
