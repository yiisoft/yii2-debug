<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\grid\GridView;

/* @var $this \yii\web\View */
/* @var $panel yii\debug\panels\UserPanel */

if ($panel->canSwitchUser()) {
    echo '<h2>Switch user</h2>';
?>
<div class="row">
    <div class="col-sm-7">
        <?php $formSet = ActiveForm::begin([
            'action' => \yii\helpers\Url::to(['user/set-identity']),
            'layout' => 'horizontal',
            'options' => [
                'style' => ($panel->canSearchUsers() ? 'display:none' : '')
            ]
        ]);
        echo $formSet->field(
            $panel->userSwitch->getUser()->identity,
            'id', ['options' => ['class' => '']])->textInput(['id' => 'user_id', 'name' => 'user_id'])
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
    <div class="col-sm-5">
        <?php
        if (!$panel->userSwitch->isMainUser()) {
            $formReset = ActiveForm::begin(['action' => \yii\helpers\Url::to(['user/reset-identity'])]);
            echo Html::submitButton('Reset to <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info">' .
                $panel->userSwitch->getMainUser()->getId() .
                '</span>', ['class' => 'btn btn-default']);
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

<?php
    if ($panel->canSearchUsers()) {
        \yii\widgets\Pjax::begin();
        echo GridView::widget([
            'dataProvider' => $panel->getUserDataProvider(),
            'filterModel' => $panel->getUsersFilterModel(),
            'options' => [
                'class' => 'grid-view table-responsive'
            ],
            'columns' => $panel->filterColumns,
            'rowOptions' => [
                    'onclick' => "$('#{$formSet->getId()} #user_id').val($(this).data('key'));" .
                        "$('#{$formSet->getId()}').submit()"
            ]
        ]);
        \yii\widgets\Pjax::end();
    }
}