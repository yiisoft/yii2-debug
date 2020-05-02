<?php

namespace yiiunit\debug\router;

use PHPUnit\Framework\TestCase;
use yii\debug\models\router\CurrentRoute;

class CurrentRouteTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDoNothingForNoMessages()
    {
        $router = new CurrentRoute();

        $this->assertSame([], $router->messages);
        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertNull($router->message);
        $this->assertSame([], $router->logs);
        $this->assertSame(0, $router->count);
        $this->assertFalse($router->hasMatch);
    }
}
