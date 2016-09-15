<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\components;

use yii\data\ArrayDataProvider;

/**
 * TimelineDataProvider implements a data provider based on a data array.
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.7
 */
class TimelineDataProvider extends ArrayDataProvider
{
    /**
     * start request, timestamp
     * @var float
     */
    public $start;

    /**
     * end request, timestamp
     * @var float
     */
    public $end;

    /**
     * request duration
     * @var float
     */
    public $duration;

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (($models = $this->allModels) === null) {
            return [];
        }
        $child = [];
        foreach ($models as $key => &$model) {
            $model['timestamp'] *= 1000;
            $model['duration'] *= 1000;
            $model['child'] = 0;
            $model['css']['width'] = $this->getWidth($model);
            $model['css']['left'] = $this->getLeft($model);
            $model['css']['color'] = $this->getColor($model);
            foreach ($child as $id => $timestamp) {
                if ($timestamp > $model['timestamp']) {
                    ++$models[$id]['child'];
                } else {
                    unset($child[$id]);
                }
            }
            $child[$key] = $model['timestamp'] + $model['duration'];
        }
        return $models;
    }

    /**
     * item, hex color
     * @param array $model
     * @return string
     */
    public function getColor($model)
    {
        $width = isset($model['css']['width']) ? $model['css']['width'] : $this->getWidth($model);
        $color = '#d6e685';
        if ($width > 20) {
            $color = '#1e6823';
        } elseif ($width > 10) {
            $color = '#44a340';
        } elseif ($width > 1) {
            $color = '#8cc665';
        }
        return $color;
    }

    /**
     * item, left percent
     * @param array $model
     * @return float
     */
    public function getLeft($model)
    {
        return $this->getTime($model) / ($this->duration / 100);
    }

    /**
     * item, duration
     * @param array $model
     * @return float
     */
    public function getTime($model)
    {
        return $model['timestamp'] - $this->start;
    }

    /**
     * item, width percent
     * @param array $model
     * @return float
     */
    public function getWidth($model)
    {
        return $model['duration'] / ($this->duration / 100);
    }

    /**
     * item, css class
     * @param array $model
     * @return string
     */
    public function getCssClass($model)
    {
        $class = 'time';
        $class .= ($model['css']['left'] + $model['css']['width'] > 50) ? ' right' : ' left';
        return $class;
    }

    /**
     * ruler items, key milliseconds, value offset left
     * @param int $line
     * @return array
     */
    public function getRulers($line = 10)
    {
        $data = [0];
        $percent = ($this->duration / 100);
        $row = $this->duration / $line;
        $precision = $row > 100 ? -2 : -1;
        for ($i = 1; $i < $line; $i++) {
            $ms = round($i * $row, $precision);
            $data[$ms] = $ms / $percent;
        }
        return $data;
    }

}