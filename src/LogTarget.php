<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\log\Target;

/**
 * The debug LogTarget is used to store logs for later use in the debugger tool
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogTarget extends Target
{
    /**
     * @var Module
     */
    public $module;
    /**
     * @var string
     */
    public $tag;


    /**
     * @param \yii\debug\Module $module
     * @param array $config
     */
    public function __construct($module, $config = [])
    {
        parent::__construct($config);
        $this->module = $module;
        $this->tag = uniqid();
    }

    /**
     * Exports log messages to a specific destination.
     * Child classes must implement this method.
     * @throws \yii\base\Exception
     */
    public function export()
    {
        $path = $this->module->dataPath;
        FileHelper::createDirectory($path, $this->module->dirMode);

        $summary = $this->collectSummary();

        $data = [];
        $exceptions = [];
        foreach ($this->module->panels as $id => $panel) {
            try {
                $panelData = $panel->save();
                if ($id === 'profiling') {
                    $summary['peakMemory'] = $panelData['memory'];
                    $summary['processingTime'] = $panelData['time'];
                }
                $data[$id] = serialize($panelData);
            } catch (\Exception $exception) {
                $exceptions[$id] = new FlattenException($exception);
            }
        }
        $data['summary'] = $summary;
        $data['exceptions'] = $exceptions;

        $this->module->getDataStorage()->setData($this->tag, $data);
    }

    /**
     * @return array
     * @see DefaultController
     */
    public function loadManifest()
    {
        return $this->module->getDataStorage()->getDataManifest();
    }

    /**
     * @return array
     * @see DefaultController
     */
    public function loadTagToPanels($tag)
    {
        $data = $this->module->getDataStorage()->getData($tag);
        $exceptions = $data['exceptions'];
        foreach ($this->module->panels as $id => $panel) {
            if (isset($data[$id])) {
                $panel->tag = $tag;
                $panel->load(unserialize($data[$id]));
            }
            if (isset($exceptions[$id])) {
                $panel->setError($exceptions[$id]);
            }
        }

        return $data;
    }


    /**
     * Processes the given log messages.
     * This method will filter the given messages with [[levels]] and [[categories]].
     * And if requested, it will also export the filtering result to specific medium (e.g. email).
     * @param array $messages log messages to be processed. See [[\yii\log\Logger::messages]] for the structure
     * of each message.
     * @param bool $final whether this method is called at the end of the current application
     * @throws \yii\base\Exception
     */
    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, $messages);
        if ($final) {
            $this->export();
        }
    }

    /**
     * Collects summary data of current request.
     * @return array
     */
    protected function collectSummary()
    {
        if (Yii::$app === null) {
            return [];
        }

        $request = Yii::$app->getRequest();
        $response = Yii::$app->getResponse();
        $summary = [
            'tag' => $this->tag,
            'url' => $request->getAbsoluteUrl(),
            'ajax' => (int) $request->getIsAjax(),
            'method' => $request->getMethod(),
            'ip' => $request->getUserIP(),
            'time' => $_SERVER['REQUEST_TIME_FLOAT'],
            'statusCode' => $response->statusCode,
            'sqlCount' => $this->getSqlTotalCount(),
        ];

        if (isset($this->module->panels['mail'])) {
            $mailFiles = $this->module->panels['mail']->getMessagesFileName();
            $summary['mailCount'] = count($mailFiles);
            $summary['mailFiles'] = $mailFiles;
        }

        return $summary;
    }

    /**
     * Returns total sql count executed in current request. If database panel is not configured
     * returns 0.
     * @return int
     */
    protected function getSqlTotalCount()
    {
        if (!isset($this->module->panels['db'])) {
            return 0;
        }
        $profileLogs = $this->module->panels['db']->getProfileLogs();

        # / 2 because messages are in couple (begin/end)

        return count($profileLogs) / 2;
    }
}
