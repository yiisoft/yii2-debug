<?php

namespace yiiunit\debug\router;

use PHPUnit\Framework\TestCase;
use yii\debug\models\router\CurrentRoute;
use yii\log\Logger;

class CurrentRouteTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDoNothingForNoMessages(): void
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

    /**
     * @test
     */
    public function shouldStoreMessageForProperOne(): void
    {
        $router = new CurrentRoute(['messages' => [['test', Logger::LEVEL_TRACE]]]);

        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertSame('test', $router->message);
        $this->assertSame([], $router->logs);
        $this->assertSame(0, $router->count);
        $this->assertFalse($router->hasMatch);
    }

    /**
     * @test
     */
    public function shouldStoreLogForProperOneAndTrueMatch(): void
    {
        $router = new CurrentRoute(
            [
                'messages' => [
                    [
                        ['rule' => 'test rule', 'match' => true],
                        999
                    ]
                ]
            ]
        );

        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertNull($router->message);
        $this->assertSame([['rule' => 'test rule', 'match' => true]], $router->logs);
        $this->assertSame(1, $router->count);
        $this->assertTrue($router->hasMatch);
    }

    /**
     * @test
     */
    public function shouldStoreLogForProperOneAndFalseMatch(): void
    {
        $router = new CurrentRoute(
            [
                'messages' => [
                    [
                        ['rule' => 'test rule', 'match' => false],
                        999
                    ]
                ]
            ]
        );

        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertNull($router->message);
        $this->assertSame([['rule' => 'test rule', 'match' => false]], $router->logs);
        $this->assertSame(1, $router->count);
        $this->assertFalse($router->hasMatch);
    }

    /**
     * @test
     */
    public function shouldSkipParent(): void
    {
        $router = new CurrentRoute(
            [
                'messages' => [
                    [
                        ['rule' => 'test rule', 'match' => false, 'parent' => 'test parent'],
                        999
                    ],
                    [
                        ['rule' => 'test parent', 'match' => false],
                        999
                    ]
                ]
            ]
        );

        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertNull($router->message);
        $this->assertSame([['rule' => 'test rule', 'match' => false, 'parent' => 'test parent']], $router->logs);
        $this->assertSame(1, $router->count);
        $this->assertFalse($router->hasMatch);
    }

    /**
     * @test
     */
    public function shouldIncreaseCounter(): void
    {
        $router = new CurrentRoute(
            [
                'messages' => [
                    [
                        ['rule' => 'test rule 1', 'match' => false],
                        999
                    ],
                    [
                        ['rule' => 'test rule 2', 'match' => false],
                        999
                    ]
                ]
            ]
        );

        $this->assertSame('', $router->route);
        $this->assertSame('', $router->action);
        $this->assertNull($router->message);
        $this->assertSame(
            [
                ['rule' => 'test rule 1', 'match' => false],
                ['rule' => 'test rule 2', 'match' => false]
            ],
            $router->logs
        );
        $this->assertSame(2, $router->count);
        $this->assertFalse($router->hasMatch);
    }
}
