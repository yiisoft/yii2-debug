<?php

namespace yiiunit\debug\router;

use yii\debug\models\router\ActionRoutes;
use yiiunit\debug\TestCase;

class ActionRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDetectPrettyUrlEnabled()
    {
        $this->mockWebApplication(
            [
                'controllerNamespace' => 'yiiunit\debug\router\controllers',
                'components' => [
                    'urlManager' => [
                        'enablePrettyUrl' => true,
                        'rules' => [
                            '<controller>/<action>' => '<controller>/<action>'
                        ]
                    ],
                ],
            ]
        );

        $routes = new ActionRoutes();
        $this->assertSame(
            [
                'yiiunit\debug\router\controllers\RestController::actions()[create] => yii\rest\CreateAction' => [
                    'route' => 'rest/create',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\RestController::actions()[delete] => yii\rest\DeleteAction' => [
                    'route' => 'rest/delete',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\RestController::actions()[index] => yii\rest\IndexAction' => [
                    'route' => 'rest/index',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\RestController::actions()[options] => yii\rest\OptionsAction' => [
                    'route' => 'rest/options',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\RestController::actions()[update] => yii\rest\UpdateAction' => [
                    'route' => 'rest/update',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\RestController::actions()[view] => yii\rest\ViewAction' => [
                    'route' => 'rest/view',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\WebController::actionFirst()' => [
                    'route' => 'web/first',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\WebController::actionSecond()' => [
                    'route' => 'web/second',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
                'yiiunit\debug\router\controllers\WebController::actions()[error] => yii\web\ErrorAction' => [
                    'route' => 'web/error',
                    'rule' => '<controller>/<action>',
                    'count' => 1,
                ],
            ],
            $routes->routes
        );
    }
}
