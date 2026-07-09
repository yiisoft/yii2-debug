<?php

/**
 * @var \yii\debug\panels\RequestContextPanel $panel
 */

$route = isset($panel->data['route']) ? $panel->data['route'] : '';
?>
<div class="yii-debug-toolbar__block">
    <a href="<?= $panel->getUrl() ?>" title="Request Context">
        Context
        <span class="yii-debug-toolbar__label">&#9881;</span>
    </a>
</div>