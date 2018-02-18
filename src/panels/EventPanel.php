<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\debug\Panel;

/**
 * EventPanel
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.14
 */
class EventPanel extends Panel
{
    /**
     * @var array current request events
     */
    private $_events = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Event::on('*', '*', function ($event) {
            /* @var $event Event */
            $eventData = [
                'time' => microtime(true),
                'name' => $event->name,
                'class' => get_class($event),
                'isStatic' => is_object($event->sender) ? '0' : '1',
                'senderClass' => is_object($event->sender) ? get_class($event->sender): $event->sender,
            ];

            $this->_events[] = $eventData;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Events';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/event/summary', [
            'panel' => $this,
            'eventCount' => count($this->data),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        $searchModel = new \yii\debug\models\search\Event();
        $dataProvider = $searchModel->search(Yii::$app->request->get(), $this->data);

        return Yii::$app->view->render('panels/event/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return $this->_events;
    }
}