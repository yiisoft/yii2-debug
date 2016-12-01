<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * toDo desc
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.8
 */

namespace yii\debug\models;


use yii\base\Model;
use yii\log\Logger;

class Router extends Model
{

    /**
     * @var []
     */
    public $messages;

    /**
     * @var string|null
     */
    public $message;

    /**
     * ```php
     * [
     *  [   toDo this name
     *      'rule' => (string),
     *      'match' => (bool),
     * ]
     * ]
     *
     * ```
     * @var array
     */
    public $logs = [];

    public $metric = 0;

    public $hasMatch = false;

    public function init()
    {
        parent::init();
        if (empty($this->messages)) {
            return;
        }
        foreach ($this->messages as $message) {
            if ($message[1] === Logger::LEVEL_TRACE) {
                $this->message = $message[0];
            } else {
                $this->logs[] = $message[0];
                ++$this->metric;
                if ($message[0]['match']) {
                    $this->hasMatch = true;
                }
            }
        }
    }

}