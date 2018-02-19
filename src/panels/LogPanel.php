<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\debug\models\search\Log;

/**
 * Debugger panel that collects and displays logs.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogPanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Logs';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/log/summary', ['data' => $this->data, 'panel' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        $searchModel = new Log();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

        return Yii::$app->view->render('panels/log/detail', [
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
        
        $messages = $target->filterMessages($target->messages, [], [], $except);
        foreach ($messages as &$message) {
            if (!is_string($message[1])) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($message[1] instanceof \Throwable || $message[1] instanceof \Exception) {
                    $message[1] = (string) $message[1];
                } else {
                    $message[1] = VarDumper::export($message[1]);
                }
            }
        }

        return ['messages' => $messages];
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param bool $refresh if need to build models from log messages and refresh them.
     * @return array[] models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data['messages'] as $message) {
                $this->_models[] = [
                    'level' => $message[0],
                    'message' => $message[1],
                    'category' => $message[2]['category'],
                    'time' => $message[2]['time'] * 1000, // time in milliseconds
                    'trace' => $message[2]['trace']
                ];
            }
        }

        return $this->_models;
    }
}
