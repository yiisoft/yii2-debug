<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\data\ArrayDataProvider;
use yii\debug\controllers\UserController;
use yii\debug\models\UserSwitch;
use yii\debug\Panel;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\User;

/**
 * Debugger panel that collects and displays user data.
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.8
 */
class UserPanel extends Panel
{

    /**
     * @var UserSwitch
     */
    public $userSwitch;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->userSwitch = new UserSwitch();

        //clone for reuse form accessControl behavior
        $mainUser = clone Yii::$app->user;
        $mainUser->setIdentity($this->userSwitch->getMainUser());

        //create and set access rule for main user
        $allowedUserSwitch = $this->module->allowedUserSwitch;
        $allowedUserSwitch['controllers'] = [$this->module->id . '/user'];
        $this->module->attachBehavior(
            'access',
            [
                'class' => AccessControl::className(),
                'only' => ['user/*'],
                'user' => $mainUser,
                'rules' => [
                    $allowedUserSwitch
                ]
            ]
        );
    }

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

        $attributes = array_keys(get_object_vars($data));
        if ($data instanceof ActiveRecord) {
            $attributes = array_keys($data->getAttributes());

            $attributeValues = [];
            foreach ($attributes as $attribute) {
                $attributeValues[$attribute] =  $data->getAttribute($attribute);
            }
            $data = $attributeValues;
        }

        return [
            'identity' => $data,
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
}
