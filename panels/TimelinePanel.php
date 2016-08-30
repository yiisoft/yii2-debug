<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\log\Logger;
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
     * @var array
     */
    protected $timestamps = [];

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
    public function getSummary()
    {
        return Yii::$app->view->render('panels/timeline/summary', ['data' => $this->data, 'panel' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $searchModel = new Timeline();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

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
            $logMessages = isset($this->module->panels['log']->data['messages']) ? $this->module->panels['log']->data['messages'] : [];
            $profilingMessages = isset($this->module->panels['profiling']->data['messages']) ? $this->module->panels['profiling']->data['messages'] : [];

            foreach ($logMessages as $key => $message) {
                if (
                    ($message[1] === Logger::LEVEL_PROFILE_BEGIN || $message[1] === Logger::LEVEL_PROFILE_END)
                    || ($message[1] === Logger::LEVEL_INFO && $message[2] == 'yii\db\Command::query')
                ) {
                    continue;
                }
                $this->_models[] = [
                    'info' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'timestamp' => ($message[3] * 1000), // time in milliseconds
                    'trace' => $message[4],
                    'duration' => null
                ];
            }

            foreach (Yii::getLogger()->calculateTimings($profilingMessages) as $profile) {
                $profile['timestamp'] = $profile['timestamp'] * 1000;
                $profile['duration'] = $profile['duration'] * 1000;
                $this->_models[] = $profile;
            }
        }
        return $this->_models;
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

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        parent::load($data);
        $this->initTimestamp();
    }

    /**
     * set timestamps
     * ```php
     * $this->timestamps[0] // start request, timestamp milliseconds
     * $this->timestamps[1] // end request, timestamp milliseconds
     * $this->timestamps[2] // request duration, milliseconds
     * ```
     */
    protected function initTimestamp()
    {
        $this->timestamps = [
            $this->data['start'] * 1000,
            $this->data['end'] * 1000,
        ];
        if (isset($this->module->panels['profiling']->data['time'])) {
            $this->timestamps[2] = $this->module->panels['profiling']->data['time'] * 1000;
        } else {
            $this->timestamps[2] = $this->timestamps[1] - $this->timestamps[0];
        }
    }

    /**
     * ruler items, key milliseconds, value offset left
     * @param int $line
     * @return array
     */
    public function getRulers($line = 10)
    {
        --$line;
        $data = [0];
        $percent = ($this->timestamps[2] / 100);
        $row = $this->timestamps[2] / $line;
        $precision = $row > 100 ? -2 : -1;
        for ($i = 1; $i < $line; $i++) {
            $ms = round($i * $row, $precision);
            $data[$ms] = $ms / $percent;
        }
        return $data;
    }

    /**
     * html attributes item element
     * @param $data
     * @param int $i
     * @return array
     */
    public function getHtmlAttribute($data, $i = 0)
    {
        $left = $this->getCssLeft($data, false);
        $class = 'time time-' . $this->getCssClass($data);
        $class .= ($left > 50) ? ' right' : ' left';
        return [
            'data-i' => $i,
            'data-toggle' => 'popover',
            'data-content' => $data['info'],
            'data-placement' => 'top',
            'title' => $data['category'] . ' ' . ($data['duration'] === null ? '? ms' : sprintf('%.1f ms', $data['duration'])),
            'class' => $class,
            'style' => 'margin-left:' . $left . '%;width:' . $this->getCssWidth($data)
        ];
    }

    /**
     * css left percent
     * @param $data
     * @param bool $percent
     * @return string|float
     */
    public function getCssLeft($data, $percent = true)
    {
        $left = $this->getTime($data) / ($this->timestamps[2] / 100);
        return $percent ? $left . '%' : $left;
    }

    /**
     * duration item
     * @param array $data
     * @return float
     */
    public function getTime($data)
    {
        return $data['timestamp'] - $this->timestamps[0];
    }

    /**
     * duration
     * @return float
     */
    public function getDuration()
    {
        return $this->timestamps[2];
    }

    /**
     * css width item
     * @param array $data
     * @return string
     */
    public function getCssWidth($data)
    {
        if ($data['duration'] === null) {
            return '1px';
        }
        return ($data['duration']) / ($this->timestamps[2] / 100) . '%';
    }

    /**
     * css class
     * @param array $data
     * @return string
     */
    public function getCssClass($data)
    {
        if ($data['duration'] !== null) {
            return 'profile';
        } else {
            return Logger::getLevelName($data['level']);
        }
    }


}