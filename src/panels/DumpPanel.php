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

/**
 * Dump panel that collects and displays debug messages (Logger::LEVEL_TRACE).
 *
 * @author Pistej <pistej2@gmail.com>
 * @author Simon Karlen <simi.albi@outlook.com>
 * @since 2.1.0
 */
class DumpPanel extends Panel
{
    /**
     * @var array the message categories to filter by. If empty array, it means
     * all categories are allowed
     */
    public $categories = ['application'];

    /**
     * @var bool whether the result should be syntax-highlighted
     */
    public $highlight = true;

    /**
     * @var int maximum depth that the dumper should go into the variable
     */
    public $depth = 10;

    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;

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

        $messages = $target->filterMessages($target->messages, Logger::LEVEL_TRACE, $this->categories, $except);

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
