<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this \yii\web\View */
/* @var $panel yii\debug\panels\UserPanel */

//Check allow switch user
$allowSwitchUser = false;

//todo plz, rewrite check
$userController = $panel->module->createController('user');
if (isset($userController[0]) && $userController[0] instanceof \yii\debug\controllers\UserController) {
    try {
        $actionSetIdentity = $userController[0]->createAction('set-identity');
        $allowSwitchUser = $panel->module->beforeAction($actionSetIdentity);
    } catch (\yii\web\ForbiddenHttpException $e) {
        $allowSwitchUser = false;
    }
}

if ($allowSwitchUser) {
?>
<div class="row">
    <div class="col-sm-6">
        <?php $formSet = ActiveForm::begin(['action' => \yii\helpers\Url::to(['user/set-identity']), 'layout' => 'horizontal']);
        echo $formSet->field(
            $panel->userSwitch,
            'user', ['options' => ['class' => 'pull-left']])->textInput(['id' => 'user_id', 'name' => 'user_id'])
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
    <div class="col-sm-6">
        <?php
        if (!$panel->userSwitch->isMainUser()) {
            $formReset = ActiveForm::begin(['action' => \yii\helpers\Url::to(['user/reset-identity'])]);
            echo Html::submitButton('Reset to Main User <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info">' .
                $panel->userSwitch->getMainUser()->getId() .
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

<?php
}