<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\base\UserException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Debugger controller
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UserController extends Controller
{
    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws UserException
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!Yii::$app->session->hasSessionId) {
            throw new UserException('Need start session');
        }
        return parent::beforeAction($action);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionSetIdentity()
    {
        $user_id = Yii::$app->request->post('user_id');
        $session = Yii::$app->session;

        if ($session->has('main_user')) {
            $mainUserId = $session->get('main_user');
        } else {
            $mainUserId = Yii::$app->user->identity->getId();
        }

        $newIdentity = Yii::$app->user->identity->findIdentity($user_id);
        if (!$newIdentity) {
            throw new BadRequestHttpException('Not found user by Indentity id');
        }

        //If are setting main user, that means reset identity
        if ($mainUserId === $user_id) {
            return $this->actionResetIdentity();
        }

        $session->set('main_user', $mainUserId);

        Yii::$app->user->switchIdentity($newIdentity);
        Yii::$app->session->set('main_user', $mainUserId);
        return $newIdentity;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionResetIdentity()
    {

        $session = Yii::$app->session;

        if ($session->has('main_user')) {
            $mainUserId = $session->get('main_user');
        } else {
            throw new BadRequestHttpException('It\'s you main Identity');
        }
        $session->remove('main_user');

        $mainIdentity = Yii::$app->user->identity->findIdentity($mainUserId);

        Yii::$app->user->switchIdentity($mainIdentity);
        Yii::$app->session->remove('main_user');
        return Yii::$app->user;
    }
}
