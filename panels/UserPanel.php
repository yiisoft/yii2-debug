<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\debug\Panel;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\IdentityInterface;

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
        $identity = Yii::$app->user->identity;

        if (!isset($identity)) {
            return ;
        }

        $authManager = Yii::$app->getAuthManager();

        $rolesProvider = null;
        $permissionsProvider = null;

        if ($authManager) {
            $roles = ArrayHelper::toArray($authManager->getRolesByUser(Yii::$app->getUser()->id));
            foreach ($roles as &$role) {
                $role['data'] = $this->dataToString($role['data']);
            }
            unset($role);
            $rolesProvider = new ArrayDataProvider([
                'allModels' => $roles,
            ]);

            $permissions = ArrayHelper::toArray($authManager->getPermissionsByUser(Yii::$app->getUser()->id));
            foreach ($permissions as &$permission) {
                $permission['data'] = $this->dataToString($permission['data']);
            }
            unset($permission);

            $permissionsProvider = new ArrayDataProvider([
                'allModels' => $permissions,
            ]);
        }

        $identityData = $this->identityData($identity);

        // If the identity is a model, let it specify the attribute labels
        if ($identity instanceof Model) {
            $attributes = [];

            foreach ($identityData as $attribute => &$value) {
                $attributes[] = [
                    'attribute' => $attribute,
                    'label' => $identity->getAttributeLabel($attribute)
                ];
            }
            unset($value);
        } else {
            // Let the DetailView widget figure the labels out
            $attributes = null;
        }

        return [
            'identity' => $identityData,
            'attributes' => $attributes,
            'rolesProvider' => $rolesProvider,
            'permissionsProvider' => $permissionsProvider,
        ];
    }

    /**
     * Converts mixed data to string
     *
     * @param mixed $data
     * @return string
     */
    protected function dataToString($data)
    {
        if (is_string($data)) {
            return $data;
        }

        return VarDumper::export($data);
    }

    /**
     * Returns the array/object that should be set on [[\yii\widgets\DetailView::model]]
     *
     * @param IdentityInterface $identity
     * @return array|object
     */
    protected function identityData(IdentityInterface $identity)
    {
        if ($identity instanceof Model) {
            return $identity->getAttributes();
        }

        return get_object_vars($identity);
    }
}
