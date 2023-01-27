<?php

namespace yii\debug\components\data;

use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\debug\Module;

class CacheDataStorage extends Component implements DataStorage
{

    /**
     * @var Module
     */
    private $module;

    /**
     * @var string
     */
    public $cacheComponent = 'cache';

    /**
     * @var string
     */
    public $cacheDebugDataKey = 'debug:';

    /**
     * @var string
     */
    public $cacheDebugManifestKey = 'debug:index';

    /**
     * @var int the maximum number of debug data files to keep. If there are more files generated,
     * the oldest ones will be removed.
     */
    public $historySize = 50;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var int
     */
    private $manifestDuration = 10000;

    /**
     * @var int
     */
    private $dataDuration = 3600;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->cache = \Yii::$app->get($this->cacheComponent);
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public function getData($tag)
    {
        $data = $this->cache->get($this->cacheDebugDataKey . $tag);
        if (empty($data)) {
            return [];
        } else {
            return unserialize($data);
        }
    }

    /**
     * @param string $tag
     * @param array  $data
     *
     * @return mixed|void
     */
    public function setData($tag, $data)
    {
        $this->cache->set($this->cacheDebugDataKey . $tag, serialize($data), $this->dataDuration);
        $this->updateIndex($tag, $data['summary'] ?: []);
    }

    /**
     * @param $forceReload
     *
     * @return array|mixed
     */
    public function getDataManifest($forceReload = false)
    {
        $manifest = $this->cache->get($this->cacheDebugManifestKey);
        if (empty($manifest)) {
            return [];
        } else {
            return unserialize($manifest);
        }
    }

    /**
     * @param Module $module
     *
     * @return mixed|void
     */
    public function setModule(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @param string $tag
     * @param        $summary
     *
     * @return void
     */
    private function updateIndex($tag, $summary)
    {
        $manifest = $this->cache->get($this->cacheDebugManifestKey);

        if (empty($manifest)) {
            $manifest = [];
        } else {
            $manifest = unserialize($manifest);
        }

        $manifest[$tag] = $summary;
        $this->gc($manifest);

        $this->cache->set($this->cacheDebugManifestKey, serialize($manifest), $this->manifestDuration);
    }


    /**
     * Removes obsolete data files
     *
     * @param array $manifest
     */
    protected function gc(&$manifest)
    {
        if (count($manifest) > $this->historySize + 10) {
            $n = count($manifest) - $this->historySize;
            foreach (array_keys($manifest) as $tag) {
                if (isset($manifest[$tag]['mailFiles'])) {
                    foreach ($manifest[$tag]['mailFiles'] as $mailFile) {
                        @unlink(Yii::getAlias($this->module->panels['mail']->mailPath) . "/$mailFile");
                    }
                }
                unset($manifest[$tag]);
                if (--$n <= 0) {
                    break;
                }
            }
        }
    }
}
