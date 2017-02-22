<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\base\UserException;
use yii\debug\models\UserSwitch;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Debugger controller
 *
 * @author Semen Dubina <yii2debug@sam002.net>
 * @since 2.0
 */
class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->session->hasSessionId) {
            throw new BadRequestHttpException('Need an active session');
        }
        return parent::beforeAction($action);
    }

    /**
     * Set new identity, switch user
     * @return \yii\web\User
     */
    public function actionSetIdentity()
    {
        $user_id = Yii::$app->request->post('user_id');

        $userSwitch = new UserSwitch();
        $userSwitch->setUser(\Yii::$app->user->identity->findIdentity($user_id));
        return Yii::$app->user;
    }

    /**
     * Reset identity, switch to main user
     * @return \yii\web\User
     */
    public function actionResetIdentity()
    {
        $userSwitch = new UserSwitch();
        $userSwitch->reset();
        return Yii::$app->user;
    }
}
