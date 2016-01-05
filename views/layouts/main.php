<?php
/* @var $this \yii\web\View */
/* @var $content string */
use yii\helpers\Html;

yii\debug\DebugAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php
if($this->context->action->id !== 'login' && $this->context->module->username!==false && $this->context->module->password!==false){
?>
<a href="<?= Url::to(['default/logout']) ?>" style="position: absolute;z-index: 1000;right: 7px;top:-5px;;border-radius:5px;padding: 11px 40px;background: #AA2075;color: white !important;border:none;font-size:14px;text-decoration: none !important;">logout</a>
<?php
}
?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
