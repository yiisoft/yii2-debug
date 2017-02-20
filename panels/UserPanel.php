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
use yii\db\ActiveRecord;

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

        $authManager = Yii::$app->getAuthManager();

        $rolesProvider = null;
        $permissionsProvider = null;

        if ($authManager) {
            $rolesProvider = new ArrayDataProvider([
                'allModels' => $authManager->getRolesByUser(Yii::$app->getUser()->id),
            ]);

            $permissionsProvider = new ArrayDataProvider([
                'allModels' => $authManager->getPermissionsByUser(Yii::$app->getUser()->id),

            ]);
        }

        $attributes = array_keys(get_object_vars($data));
        if ($data instanceof ActiveRecord) {
            $attributes = array_keys($data->getAttributes());
        }

        foreach ($attributes as $attribute) {
                $dataIdentity[$attribute] =  $data->getAttribute($attribute);
        }

        return [
            'identity' => $dataIdentity,
            'attributes' => $attributes,
            'rolesProvider' => $rolesProvider,
            'permissionsProvider' => $permissionsProvider,
        ];
    }
}
