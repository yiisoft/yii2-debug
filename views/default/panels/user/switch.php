<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$switch = new \yii\debug\models\UserSwitch();
?>
<div class="row">
<div class="col-lg-4">
<?php $formSet = ActiveForm::begin(['action' => \yii\helpers\Url::to(['user/set-identity']), 'layout' => 'horizontal']);
echo $formSet->field(
    $switch,
    'user', [ 'options' => ['class' => 'pull-left']])->textInput(['id' => 'user_id', 'name' => 'user_id'])
    ->label('Switch User');
echo Html::submitButton('Switch', ['class' => 'btn btn-primary']);
ActiveForm::end();

$script = <<< JS
    var sendSetIdentity = function(e) {
        var form = $(this);
        var formData = form.serialize();
        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                window.top.location.reload();
            },
            error: function (data) {
                form.yiiActiveForm('updateMessages', data.responseJSON, true);
            }
        });
    };
    $('#{$formSet->getId()}').on('beforeSubmit', sendSetIdentity)
    .on('submit', function(e){
        e.preventDefault();
    });
JS;

$this->registerJs($script, yii\web\View::POS_READY);
?>

</div>
<div class="col-lg-4">
<?php
if (Yii::$app->session->has('main_user')) {
    $formReset = ActiveForm::begin(['action' => \yii\helpers\Url::to(['user/reset-identity'])]);
    echo Html::submitButton('Reset to Main User <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info">'.
    $switch->getMainUser()->getId().
    '</span>', ['class' => 'btn btn-success']);
    ActiveForm::end();

$scriptReset = <<< JS
    $('#{$formReset->getId()}').on('beforeSubmit', sendSetIdentity)
    .on('submit', function(e){
        e.preventDefault();
    });
JS;

    $this->registerJs($scriptReset, yii\web\View::POS_READY);

}
?>
</div>
</div>
<hr/>

