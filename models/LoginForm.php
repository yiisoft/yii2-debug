<?php
namespace yii\debug\models;
use yii\base\Model;
class LoginForm extends Model{
	public $username;
	public $password;
	public function rules() {
		return [
				[['username','password'],'required']
		];
	}
	public function login($username,$password) {
		if($this->validate()){
			if($this->username === $username && $this->password === $password){
				return true;
			}
			$this->addError('username','username or password is incurrect.');
		}
		return false;
	}
}
