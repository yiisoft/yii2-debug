<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\components\data;

use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\debug\Module;
use yii\di\Instance;

/**
 *  CacheDataStorage
 */
class CacheDataStorage extends Component implements DataStorage
{
    /**
     * @var Module Debug module instance
     */
    private $module;

    /**
     * @var string Cache component di identifier
     */
    public $cacheComponent = 'cache';

    /**
     * @var string Cache data key for debug data
     */
    public $cacheDebugDataKey = 'debug:';

    /**
     * @var string Cache data key for manifest
     */
    public $cacheDebugManifestKey = 'debug:index';

    /**
     * @var int the maximum number of debug data files to keep. If there are more files generated,
     * the oldest ones will be removed.
     */
    public $historySize = 50;

    /**
     * @var Cache Cache component instance
     */
    private $cache;

    /**
     * @var int Manifest cache data ttl
     */
    public $manifestDuration = 10000;

    /**
     * @var int  Debug cache data ttl
     */
    public $dataDuration = 3600;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->cache = Instance::ensure($this->cacheComponent,'yii\caching\Cache');
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public function getData($tag)
    {
        return $this->cache->exists($this->cacheDebugDataKey . $tag) ? unserialize($this->cache->get($this->cacheDebugDataKey . $tag)) : [];
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
        return $this->cache->exists($this->cacheDebugManifestKey) ? unserialize($this->cache->get($this->cacheDebugManifestKey)) : [];
    }

    /**
     * @param Module $module
     *
     * @return mixed|void
     */
    public function setModule($module)
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
