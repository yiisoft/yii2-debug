<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\debug\models\search\Timeline;
use yii\base\InvalidConfigException;

/**
 * Debugger panel that collects and displays timeline data.
 *
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.7
 */
class TimelinePanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->module->panels['profiling'])) {
            throw new InvalidConfigException('Unable to determine the profiling panel');
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Timeline';
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $searchModel = new Timeline();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels(), $this->getTimestamps());

        return Yii::$app->view->render('panels/timeline/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param boolean $refresh if need to build models from log messages and refresh them.
     * @return array models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];
            if (isset($this->module->panels['profiling']->data['messages'])) {
                $this->_models = Yii::getLogger()->calculateTimings($this->module->panels['profiling']->data['messages']);
            }
        }
        return $this->_models;
    }

    /**
     * Returns timestamps array: start, end and duration in milliseconds
     * @return array
     */
    protected function getTimestamps()
    {
        $timestamps = [
            $this->data['start'] * 1000,
            $this->data['end'] * 1000,
        ];
        if (isset($this->module->panels['profiling']->data['time'])) {
            $timestamps[2] = $this->module->panels['profiling']->data['time'] * 1000;
        } else {
            $timestamps[2] = $timestamps[1] - $timestamps[0];
        }
        return $timestamps;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        return [
            'start' => YII_BEGIN_TIME,
            'end' => microtime(true),
        ];
    }

}