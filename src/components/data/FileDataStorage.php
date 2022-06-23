<?php

namespace yii\debug\components\data;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\debug\FlattenException;
use yii\debug\Module;
use yii\helpers\FileHelper;

class FileDataStorage extends Component implements DataStorage
{
    /**
     * @var string
     */
    public $dataPath = '@runtime/debug';

    /**
     * @var int the permission to be set for newly created directories.
     * This value will be used by PHP [[chmod()]] function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     * @since 2.0.6
     */
    public $dirMode = 0775;

    /**
     * @var int the permission to be set for newly created debugger data files.
     * This value will be used by PHP [[chmod()]] function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     * @since 2.0.6
     */
    public $fileMode;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var int the maximum number of debug data files to keep. If there are more files generated,
     * the oldest ones will be removed.
     */
    public $historySize = 50;

    /**
     * @var array
     */
    private $_manifest;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->dataPath = Yii::getAlias($this->dataPath);
    }

    /**
     * @param $tag
     *
     * @return array
     */
    public function getData($tag)
    {
        $dataFile = $this->dataPath . "/$tag.data";
        return unserialize(file_get_contents($dataFile));
    }

    /**
     * @param string $tag
     * @param array  $data
     *
     * @return void
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function setData($tag, $data)
    {
        $path = $this->dataPath;
        FileHelper::createDirectory($path, $this->dirMode);
        $dataFile = "$path/{$tag}.data";

        file_put_contents($dataFile, serialize($data));
        if ($this->fileMode !== null) {
            @chmod($dataFile, $this->fileMode);
        }

        $indexFile = "$path/index.data";
        $this->updateIndexFile($indexFile, $tag, $data['summary'] ?: []);
    }

    /**
     * @param $forceReload
     *
     * @return array|mixed
     */
    public function getDataManifest($forceReload = false)
    {
        if ($this->_manifest === null || $forceReload) {
            if ($forceReload) {
                clearstatcache();
            }
            $indexFile = $this->dataPath . '/index.data';

            $content = '';
            $fp = @fopen($indexFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $content = fread($fp, filesize($indexFile));
                @flock($fp, LOCK_UN);
                fclose($fp);
            }

            if ($content !== '') {
                $this->_manifest = array_reverse(unserialize($content), true);
            } else {
                $this->_manifest = [];
            }
        }

        return $this->_manifest;
    }


    /**
     * Updates index file with summary log data
     *
     * @param string $indexFile path to index file
     * @param array  $summary   summary log data
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function updateIndexFile($indexFile, $tag, $summary)
    {
        touch($indexFile);
        if (($fp = @fopen($indexFile, 'r+')) === false) {
            throw new InvalidConfigException("Unable to open debug data index file: $indexFile");
        }
        @flock($fp, LOCK_EX);
        $manifest = '';
        while (($buffer = fgets($fp)) !== false) {
            $manifest .= $buffer;
        }
        if (!feof($fp) || empty($manifest)) {
            // error while reading index data, ignore and create new
            $manifest = [];
        } else {
            $manifest = unserialize($manifest);
        }

        $manifest[$tag] = $summary;
        $this->gc($manifest);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, serialize($manifest));

        @flock($fp, LOCK_UN);
        @fclose($fp);

        if ($this->fileMode !== null) {
            @chmod($indexFile, $this->fileMode);
        }
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
                $file = $this->dataPath . "/$tag.data";
                @unlink($file);
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


    public function setModule(Module $module)
    {
        $this->module = $module;
    }
}
