<?php

namespace yiiunit\debug\router;

use yii\debug\models\router\CurrentRoute;
use yii\debug\models\router\RouterRules;
use yii\web\UrlRule;
use yiiunit\debug\TestCase;

class RouterRulesTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDetectPrettyUrlEnabled()
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'enablePrettyUrl' => true,
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertTrue($router->prettyUrl);
        $this->assertFalse($router->strictParsing);
        $this->assertNull($router->suffix);
    }

    /**
     * @test
     */
    public function shouldDetectPrettyUrlDisabled()
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'enablePrettyUrl' => false,
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertFalse($router->prettyUrl);
        $this->assertFalse($router->strictParsing);
        $this->assertNull($router->suffix);
    }

    /**
     * @test
     */
    public function shouldDetectStrictParsingEnabled()
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'enableStrictParsing' => true,
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertFalse($router->prettyUrl);
        $this->assertTrue($router->strictParsing);
        $this->assertNull($router->suffix);
    }

    /**
     * @test
     */
    public function shouldDetectStrictParsingDisabled()
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'enableStrictParsing' => false,
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertFalse($router->prettyUrl);
        $this->assertFalse($router->strictParsing);
        $this->assertNull($router->suffix);
    }

    /**
     * @test
     */
    public function shouldDetectGlobalSuffix()
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'suffix' => 'test',
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertFalse($router->prettyUrl);
        $this->assertFalse($router->strictParsing);
        $this->assertSame('test', $router->suffix);
    }

    public function providerForWebRules()
    {
        return [
            'simple' => [
                ['rule' => 'route'],
                [[
                    'name' => 'rule',
                    'route' => 'route',
                    'verb' => null,
                    'suffix' => null,
                    'mode' => null,
                    'type' => null
                ]]
            ],
            'simple verb' => [
                ['GET rule' => 'route'],
                [[
                    'name' => 'rule',
                    'route' => 'route',
                    'verb' => ['GET'],
                    'suffix' => null,
                    'mode' => null,
                    'type' => null
                ]]
            ],
            'simple verb parse' => [
                ['POST rule' => 'route'],
                [[
                    'name' => 'rule',
                    'route' => 'route',
                    'verb' => ['POST'],
                    'suffix' => null,
                    'mode' => 'parsing only',
                    'type' => null
                ]]
            ],
            'custom' => [
                [['class' => 'yiiunit\debug\router\CustomRuleStub']],
                [[
                    'name' => 'yiiunit\debug\router\CustomRuleStub',
                    'route' => null,
                    'verb' => null,
                    'suffix' => null,
                    'mode' => null,
                    'type' => null
                ]]
            ],
            'creation only' => [
                [['pattern' => 'pattern', 'route' => 'route', 'mode' => UrlRule::CREATION_ONLY]],
                [[
                    'name' => 'pattern',
                    'route' => 'route',
                    'verb' => null,
                    'suffix' => null,
                    'mode' => 'creation only',
                    'type' => null
                ]]
            ],
            'unknown mode' => [
                [['pattern' => 'pattern', 'route' => 'route', 'mode' => 999]],
                [[
                    'name' => 'pattern',
                    'route' => 'route',
                    'verb' => null,
                    'suffix' => null,
                    'mode' => 'unknown',
                    'type' => null
                ]]
            ],
            'suffix' => [
                [['pattern' => 'pattern', 'route' => 'route', 'suffix' => '.html']],
                [[
                    'name' => 'pattern',
                    'route' => 'route',
                    'verb' => null,
                    'suffix' => '.html',
                    'mode' => null,
                    'type' => null
                ]]
            ],
            'group' => [
                [[
                    'class' => 'yii\web\GroupUrlRule',
                    'prefix' => 'admin',
                    'rules' => [
                        'login' => 'user/login',
                        'logout' => 'user/logout',
                    ],
                ]],
                [
                    [
                        'name' => 'admin/login',
                        'route' => 'admin/user/login',
                        'verb' => null,
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'GROUP'
                    ],
                    [
                        'name' => 'admin/logout',
                        'route' => 'admin/user/logout',
                        'verb' => null,
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'GROUP'
                    ]
                ]
            ],
            'rest' => [
                [['class' => 'yii\rest\UrlRule', 'controller' => 'user']],
                [
                    [
                        'name' => 'users/<id:\d[\d,]*>',
                        'route' => 'user/update',
                        'verb' => ['PUT', 'PATCH'],
                        'suffix' => null,
                        'mode' => 'parsing only',
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users/<id:\d[\d,]*>',
                        'route' => 'user/delete',
                        'verb' => ['DELETE'],
                        'suffix' => null,
                        'mode' => 'parsing only',
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users/<id:\d[\d,]*>',
                        'route' => 'user/view',
                        'verb' => ['GET', 'HEAD'],
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users',
                        'route' => 'user/create',
                        'verb' => ['POST'],
                        'suffix' => null,
                        'mode' => 'parsing only',
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users',
                        'route' => 'user/index',
                        'verb' => ['GET', 'HEAD'],
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users/<id:\d[\d,]*>',
                        'route' => 'user/options',
                        'verb' => [],
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'REST'
                    ],
                    [
                        'name' => 'users',
                        'route' => 'user/options',
                        'verb' => [],
                        'suffix' => null,
                        'mode' => null,
                        'type' => 'REST'
                    ],
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerForWebRules
     * @param array $rules
     * @param array $expected
     */
    public function shouldProperlyScanWebRule($rules, $expected)
    {
        $this->mockWebApplication(
            [
                'components' => [
                    'urlManager' => [
                        'enablePrettyUrl' => true,
                        'rules' => $rules
                    ],
                ],
            ]
        );

        $router = new RouterRules();
        $this->assertSame($expected, $router->rules);
    }
}
