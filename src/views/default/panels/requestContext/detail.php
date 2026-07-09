<?php

use yii\helpers\Html;

/**
 * @var \yii\debug\panels\RequestContextPanel $panel
 */

$contextRows = $panel->getContextRows();
$behaviors = isset($panel->data['behaviors']) ? $panel->data['behaviors'] : [];
$routeParams = isset($panel->data['routeParams']) ? $panel->data['routeParams'] : [];
$viewTree = isset($panel->data['viewTree']) ? $panel->data['viewTree'] : [];

?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Request Context</h1>
    <div>
        <span id="context-panel-copied" class="text-success mr-2 font-weight-bold" hidden>Copied!</span>
        <button class="btn btn-sm btn-success" id="context-panel-copy-btn">Copy as text</button>
    </div>
</div>

<textarea id="context-panel-text" class="form-control mb-3" rows="5" readonly><?= Html::encode($panel->buildPlainText()) ?></textarea>

<h3>Request Context</h3>
<div class="table-responsive">
    <table class="table table-condensed table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th style="width:15%">Name</th>
                <th>
                    Value
                    <small class="text-muted font-weight-normal">(click to copy)</small>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contextRows as $name => $value): ?>
                <tr>
                    <th><?= Html::encode($name) ?></th>
                    <td><?= $value ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($behaviors)): ?>
    <h3>Controller Behaviors</h3>
    <div class="table-responsive">
        <table class="table table-condensed table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th style="width:15%">Name</th>
                    <th>
                        Class
                        <small class="text-muted font-weight-normal">(click to copy)</small>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($behaviors as $behavior): ?>
                    <tr>
                        <td><?= Html::encode($behavior['name']) ?></td>
                        <td><?= $panel->renderCopyableValue($behavior['class']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (!empty($routeParams)): ?>
    <h3>Route Parameters</h3>
    <div class="table-responsive">
        <table class="table table-condensed table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th style="width:15%">Name</th>
                    <th>
                        Value
                        <small class="text-muted font-weight-normal">(click to copy)</small>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routeParams as $name => $value): ?>
                    <tr>
                        <td><?= Html::encode($name) ?></td>
                        <td><?= $panel->renderCopyableValue(is_array($value) ? json_encode($value) : $value) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (!empty($viewTree)): ?>
    <h3>View Tree</h3>
    <?= $panel->renderViewTree($viewTree) ?>
<?php endif; ?>

<script>
    document.querySelectorAll('.copyable-value').forEach(function(el) {
        el.addEventListener('click', function() {
            var text = el.textContent;
            var status = el.nextElementSibling;

            function showStatus() {
                if (status) {
                    status.hidden = false;
                    setTimeout(function() {
                        status.hidden = true;
                    }, 1500);
                }
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(showStatus);
            } else {
                var range = document.createRange();
                range.selectNodeContents(el);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                document.execCommand('copy');
                showStatus();
            }
        });
    });

    document.getElementById('context-panel-copy-btn').addEventListener('click', function() {
        var textarea = document.getElementById('context-panel-text');
        var status = document.getElementById('context-panel-copied');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textarea.value).then(onCopied).catch(function() {
                fallbackCopy(textarea);
            });
        } else {
            fallbackCopy(textarea);
        }

        function fallbackCopy(el) {
            el.select();
            document.execCommand('copy');
            onCopied();
        }

        function onCopied() {
            status.hidden = false;
            setTimeout(function() {
                status.hidden = true;
            }, 2000);
        }
    });
</script>
