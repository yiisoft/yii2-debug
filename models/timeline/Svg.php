<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\models\timeline;

use yii\base\Model;
use yii\debug\panels\TimelinePanel;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.8
 */
class Svg extends Model
{
    /**
     * @var int Max coordinate
     */
    public $x = 100;
    /**
     * @var int Max coordinate
     */
    public $y = 40;
    /**
     * @var array
     */
    public $listenMessages = ['log', 'profiling'];
    /**
     * @var string
     */
    public $template = '<svg width="{x}%" height="{y}" viewBox="0 0 {x} {y}" preserveAspectRatio="none"><defs>{linearGradient}</defs><path  x="0" y="0" transform="scale(1 1)" d="{d}" style="stroke: none; fill: url(#gradient);"/></svg>';
    /**
     * [
     *  [x, y]
     * ]
     * @var \SplMinHeap
     */
    protected $points;
    /**
     * @var TimelinePanel
     */
    protected $panel;


    /**
     * @inheritdoc
     */
    public function __construct(TimelinePanel $panel, $config = [])
    {
        $this->panel = $panel;
        $this->points = new \SplMinHeap();
        parent::__construct($config);
        foreach ($this->listenMessages as $panel) {
            if (isset($this->panel->module->panels[$panel]->data['messages'])) {
                $this->addPoints($this->panel->module->panels[$panel]->data['messages']);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->points->count() === 0) {
            return '';
        }

        return strtr($this->template, [
            '{x}' => $this->x,
            '{y}' => $this->y,
            '{d}' => $this->d(),
            '{linearGradient}' => $this->linearGradient()
        ]);
    }

    /**
     * @return bool
     */
    public function hasPoints()
    {
        return ($this->points->count() !== 0);
    }

    /**
     * @param $messages
     */
    protected function addPoints($messages)
    {
        $yOne = $this->panel->memory / 100; // - 1 percent |rename
        $yMax = $this->y / 100; // 1 percent coordinate |rename

        $xOne = $this->panel->duration / $this->x;

        foreach ($messages as $message) {
            if (!isset($message[5])) {
                break;
            }
            $x = ($message[3] * 1000 - $this->panel->start) / $xOne;
            $y = $this->y - ($message[5] / $yOne * $yMax);
            $this->points->insert([$x, $y]);
        }
    }

    /**
     * @return string|null
     */
    protected function d()
    {
        $str = "M0,{$this->y}";
        foreach ($this->points as $point) {
            $str .= ' L' . $point[0] . ',' . $point[1];
        }
        $str .= ' L' . ($this->x - 0.001) . ',' . $point[1]; //
        return "{$str} L{$this->x},{$this->y} z";
    }

    /**
     * @return string
     */
    protected function linearGradient()
    {
        $gradient = '<linearGradient id="gradient" x1="0" x2="0" y1="1" y2="0">';
        foreach ($this->panel->getGradient() as $percent => $color) {
            $gradient .= '<stop offset="' . $percent . '%" stop-color="' . $color . '"></stop>';
        }
        return $gradient . '</linearGradient>';
    }

}