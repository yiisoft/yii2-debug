<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\data\ArrayDataProvider;
use yii\debug\Panel;

/**
 * Debugger panel that collects and displays user data.
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.8
 */
class UserPanel extends Panel
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'User';
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function save()
    {
        $data = Yii::$app->user->identity;

        if (!isset($data)) {
            return ;
        }

        $rolesProvider = new ArrayDataProvider([
            'allModels' => Yii::$app->getAuthManager()->getRolesByUser(Yii::$app->getUser()->id),
        ]);

        $permissionsProvider = new ArrayDataProvider([
            'allModels' => Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->id),

        ]);

        return [
            'identity' => $data,
            'attributes' => array_keys(get_object_vars($data)),
            'cookie' => Yii::$app->getRequest()->getCookies()->get(Yii::$app->user->identityCookie['name']),
            'rolesProvider' => $rolesProvider,
            'permissionsProvider' => $permissionsProvider,
        ];
    }
}
