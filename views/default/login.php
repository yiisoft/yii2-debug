<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\YiiAsset;
use yii\bootstrap\BootstrapAsset;

/* @var $this yii\web\View */

$this->title = 'Developer Login';
?>
<div style="text-align: center;margin: 140px auto;width: 30%;border:1px solid #aaaaaa;border-radius:14px;box-shadow:0 0 70px rgba(196,196,196,0.3);padding: 40px;">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form','successCssClass'=>'','errorCssClass'=>'',
    	'fieldConfig'=>[
    			'errorOptions'=>['style'=>'color:red;','class'=>'error'],
    			'inputOptions'=>['style'=>'border:1px #aaaaaa solid;border-radius:5px;padding:7px;margin-bottom:10px;'],
    			'template'=>"{input}\n{error}"

    ]

    ]); ?>
    <?= $form->field($model, 'username')->textInput(['placeholder'=>'username...']) ?>
    <?= $form->field($model, 'password')->passwordInput(['placeholder'=>'password...']) ?>
    <div class="form-group">
        <div style="text-align: center;">
            <?= Html::submitButton('Login', ['style' => 'border-radius:5px;padding: 11px 40px;background: green;color: lime;border:none;font-size:14px;cursor:pointer;', 'name' => 'login-button']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
