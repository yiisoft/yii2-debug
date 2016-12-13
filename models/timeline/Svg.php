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
    public $y = 28;
    /**
     * @var array
     */
    public $listenMessages = ['log', 'profiling'];
    /**
     * @var string
     */
    public $template = '<svg width="{x}%" height="{y}" viewBox="0 0 {x} {y}" preserveAspectRatio="none">
            <defs>
                <linearGradient id="gradient" x1="0" x2="0" y1="1" y2="0">
                    <stop offset="10%" stop-color="#d6e685"></stop>
                    <stop offset="33%" stop-color="#8cc665"></stop>
                    <stop offset="66%" stop-color="#44a340"></stop>
                    <stop offset="90%" stop-color="#1e6823"></stop>
                </linearGradient>
                <mask id="sparkline" x="0" y="0" width="{x}%" height="{y}">
                    <polyline transform="translate(0, {y}) scale(1,-1)" points="{points}" fill="transparent" stroke="#8cc665" stroke-width="2"/>
                </mask>
            </defs>
            <g transform="translate(0, 2.0)">
                <rect x="0" y="0" width="{x}%" height="{y}" style="stroke: none; fill: url(#gradient); mask: url(#sparkline)"></rect>
            </g>
        </svg>';
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
            '{points}' => $this->getPoints()
        ]);
    }

    /**
     * @return string|null
     */
    public function getPoints()
    {
        $str = '';
        foreach ($this->points as $point) {
            $str .= ' ' . $point[0] . ',' . $point[1];
        }
        if ($str === '') {
            return null;
        }
        return "0,0{$str} 100,{$point[1]}";
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
            $y = $message[5] / $yOne * $yMax;
            $this->points->insert([$x, $y]);
        }
    }

}