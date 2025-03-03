<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use Yii;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\Yii\Debug\Api\Debug\Controller\DebugController;
use Yiisoft\Yii\Debug\Api\Debug\Repository\CollectorRepository;
use Yiisoft\Yii\Debug\Storage\FileStorage;

/**
 * Debugger controller provides browsing over available debug logs.
 *
 * @see \yii\debug\Panel
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ApiController extends Controller
{
    public $layout = 'main';
    /**
     * @var \yii\debug\Module owner module.
     */
    public $module;

    public function behaviors()
    {
        return [
            'cors' => [
                'class' => Cors::class,
                'actions' => '*'
            ]
        ];
    }

    public function actions()
    {
        $actions = [];
        foreach ($this->module->panels as $panel) {
            $actions = array_merge($actions, $panel->actions);
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }

    /**
     * Index action
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $dataResponseFactory = Yii::createObject(DataResponseFactory::class, [
            'responseFactory' => Yii::createObject(ResponseFactory::class, []),
            'streamFactory' => Yii::createObject(StreamFactory::class, []),
        ]);
        $collectorRepository = Yii::createObject(CollectorRepository::class, [
            'storage' => new FileStorage(Yii::getAlias('@runtime/debug')),
        ]);
        $controller = Yii::createObject(DebugController::class, [
            'responseFactory' => $dataResponseFactory,
            'collectorRepository' => $collectorRepository,
        ]);
        $result = $controller->index();
        return (new JsonDataResponseFormatter())
            ->format(
                $dataResponseFactory->createResponse([
                    'data' => $collectorRepository->getSummary(),
                ])
            )->getBody();
    }
}
