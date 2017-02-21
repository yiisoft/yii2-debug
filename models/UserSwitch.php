<?php
/**
 * Author: Semen Dubina
 * Date: 21.02.17
 * Time: 3:27
 */

namespace yii\debug\models;


use yii\base\Model;
use yii\web\IdentityInterface;
/**
 * User model
 *
 * @property IdentityInterface $user
 * @property IdentityInterface $mainUser
 */
class UserSwitch extends Model
{
    /**
     * @var IdentityInterface
     */
    private $user;

    /**
     * @var IdentityInterface
     */
    private $mainUser;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user', 'mainUser'], 'safe']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'user' => 'Current User',
            'mainUser' => 'frontend', 'Main User',
        ];
    }

    /**
     * Get current user
     * @return null|IdentityInterface
     */
    public function getUser()
    {
        if (empty($this->user)) {
            $this->user = \Yii::$app->user->identity;
        }
        return $this->user;
    }

    /**
     * Get main user
     * @return IdentityInterface
     */
    public function getMainUser()
    {
        $session = \Yii::$app->session;
        if (empty($this->mainUser)) {

            if ($session->has('main_user')) {
                $mainUserId = $session->get('main_user');
            } else {
                $mainUserId = \Yii::$app->user->identity->getId();
            }

            $this->mainUser = \Yii::$app->user->identity->findIdentity($mainUserId);
        }

        return $this->mainUser;
    }

    /**
     * Switch user
     * @param IdentityInterface $user
     */
    public function setUser(IdentityInterface $user)
    {

        \Yii::$app->user->switchIdentity($user);

        if ($user !== $this->getMainUser()) {
            \Yii::$app->session->set('main_user', $this->getMainUser()->getId());
        } else {
            \Yii::$app->session->remove('main_user');
        }
    }

    /**
     * Reset to main user
     */
    public function reset()
    {
        $this->setUser($this->getMainUser());
    }

}