<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use yii\helpers\Html;

class RequestContextValueRenderer
{
    /**
     * @param string $value
     * @return string
     */
    public function renderCopyableValue($value)
    {
        return Html::tag('code', Html::encode($value), ['class' => 'copyable-value', 'style' => 'cursor:pointer'])
            . ' ' . Html::tag('span', 'Copied!', [
                'class' => 'copyable-status text-success font-weight-bold',
                'hidden' => true,
            ]);
    }

    /**
     * @param string $value
     * @param int $depth
     * @param int $count
     * @return string
     */
    public function renderIndentedCopyableValue($value, $depth, $count)
    {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
        $countSuffix = $count > 1 ? ' (' . $count . ')' : '';

        return '<code class="copyable-value" style="cursor:pointer">'
            . $indent . Html::encode($value) . $countSuffix
            . '</code> '
            . Html::tag('span', 'Copied!', [
                'class' => 'copyable-status text-success font-weight-bold',
                'hidden' => true,
            ]);
    }
}
