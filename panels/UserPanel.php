<?php

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;

class UserPanel extends Panel
{
    public function getName()
    {
        return 'User';
    }

    public function getSummary()
    {
        return Yii::$app->view->render('panels/user/summary', ['panel' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return Yii::$app->view->render('panels/user/detail', ['panel' => $this]);
    }

    public function save()
    {
        $data = Yii::$app->user->identity;

        if (!isset($data)) {
            return ;
        }

        return [
            'identity' => $data,
            'attributes' => array_keys(get_object_vars($data))
        ];
    }
}
