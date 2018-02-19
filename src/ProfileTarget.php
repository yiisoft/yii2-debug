<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\profile\Target;

/**
 * ProfileTarget
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class ProfileTarget extends Target
{
    /**
     * @var array complete profiling messages.
     * @see \yii\profile\Profiler::$messages
     */
    public $messages = [];


    /**
     * {@inheritdoc}
     */
    public function export(array $messages)
    {
        $this->messages = $messages;
    }
}