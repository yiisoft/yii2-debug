<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\log\Logger;
use yii\debug\models\search\Log;
use yii\debug\Panel;
use yii\helpers\VarDumper;

/**
 * Dump panel that collects and displays debug messages (Logger::LEVEL_TRACE).
 *
 * @author Pistej <pistej2@gmail.com>
 * @since 2.0.14
 */
class DumpPanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;

    /**
     * Merge user setting with default settings
     */
    public function init()
    {
        parent::init();
        $this->module->dumpModule = array_merge([
            'categories' => ['application'],
            'depth' => 10,
            'highlight' => false,
        ], $this->module->dumpModule);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Dump';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/dump/summary', ['panel' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        $searchModel = new Log();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

        return Yii::$app->view->render('panels/dump/detail', [
            'dataProvider' => $dataProvider,
            'panel' => $this,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $target = $this->module->logTarget;
        $except = [];
        if (isset($this->module->panels['router'])) {
            $except = $this->module->panels['router']->getCategories();
        }

        $messages = $target->filterMessages($target->messages, Logger::LEVEL_TRACE, $this->module->dumpModule['categories'], $except);
        foreach ($messages as &$message) {
            $message[0] = VarDumper::export($message[0]);
        }

        return $messages;
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param bool $refresh if need to build models from log messages and refresh them.
     * @return array models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data as $message) {
                $this->_models[] = [
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'time' => $message[3] * 1000, // time in milliseconds
                    'trace' => $message[4]
                ];
            }
        }

        return $this->_models;
    }
}
