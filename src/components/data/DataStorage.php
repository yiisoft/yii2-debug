<?php

namespace yii\debug\components\data;

use yii\debug\Module;

interface DataStorage
{
    /**
     * @param string $tag
     *
     * @return array
     */
    public function getData($tag);

    /**
     * @param string $tag
     * @param array  $data
     *
     * @return mixed
     */
    public function setData($tag,$data);

    /**
     * @param $forceReload
     *
     * @return mixed
     */
    public function getDataManifest($forceReload = false);

    /**
     * @param Module $module
     *
     * @return mixed
     */
    public function setModule(Module $module);
}
