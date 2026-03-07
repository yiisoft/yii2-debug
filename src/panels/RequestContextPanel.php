<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\View;
use yii\base\ViewEvent;
use yii\debug\Panel;
use yii\helpers\Html;

/**
 * Debugger panel that collects and displays request context:
 * controller/action, layout, rendered views tree and applied behaviors.
 *
 * @since 2.1.28
 */
class RequestContextPanel extends Panel
{
    /**
     * @var list<string>
     */
    private $_renderedViews = [];

    /**
     * @var list<array{file: string, short: string, children: list<mixed>}>
     */
    private $_viewTree = [];

    /**
     * @var list<array{file: string, short: string, children: list<mixed>}>
     */
    private $_renderStack = [];

    /**
     * {@inheritdoc}
     * @return void
     */
    public function init()
    {
        parent::init();

        Event::on(View::class, View::EVENT_BEFORE_RENDER, function (ViewEvent $event) {
            $this->_renderStack[] = [
                'file' => $event->viewFile,
                'short' => $this->shortenPath($event->viewFile),
                'children' => [],
            ];
        });

        Event::on(View::class, View::EVENT_AFTER_RENDER, function (ViewEvent $event) {
            $this->_renderedViews[] = $event->viewFile;
            $node = array_pop($this->_renderStack);
            if ($node === null) {
                return;
            }
            if (empty($this->_renderStack)) {
                $this->_viewTree[] = $node;
            } else {
                $this->_renderStack[count($this->_renderStack) - 1]['children'][] = $node;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Context';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/requestContext/summary', ['panel' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        return Yii::$app->view->render('panels/requestContext/detail', ['panel' => $this]);
    }

    /**
     * @param string $value
     * @return string
     */
    public function renderCopyableValue($value)
    {
        return Html::tag('code', Html::encode($value), ['class' => 'copyable-value', 'style' => 'cursor:pointer'])
            . ' ' . Html::tag('span', 'Copied!', [
                'class' => 'copyable-status text-success font-weight-bold',
                'hidden' => true,
            ]);
    }

    /**
     * @return array<string, string|int|null>
     */
    public function getContextRows()
    {
        $data = $this->data;
        $rows = [
            'Route' => $this->renderCopyableValue($data['route']),
            'Controller' => $data['controllerClass'] !== null ? $this->renderCopyableValue($data['controllerClass']) : null,
            'Controller File' => $data['controllerFile'] !== null ? $this->renderCopyableValue($data['controllerFile']) : null,
            'Action' => $data['actionMethod'] !== null ? $this->renderCopyableValue($data['actionMethod']) : null,
            'Action Line' => $data['actionLine'],
            'Layout' => $data['layout'] !== null ? $this->renderCopyableValue($data['layout']) : null,
            'Views Rendered' => $data['viewCount'],
        ];

        return array_filter($rows, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $controller = Yii::$app->controller;

        return [
            'controllerClass' => $controller !== null ? get_class($controller) : null,
            'controllerFile' => $this->resolveControllerFile(),
            'actionId' => $controller !== null && $controller->action !== null ? $controller->action->id : null,
            'actionMethod' => $this->resolveActionMethod(),
            'actionLine' => $this->resolveActionLine(),
            'layout' => $this->resolveLayout(),
            'route' => Yii::$app->requestedAction
                ? Yii::$app->requestedAction->getUniqueId()
                : Yii::$app->requestedRoute,
            'routeParams' => $this->resolveRouteParams(),
            'behaviors' => $this->resolveBehaviors(),
            'viewTree' => $this->_viewTree,
            'viewCount' => count($this->_renderedViews),
        ];
    }

    /**
     * Renders the view tree as nested HTML list.
     *
     * @param array $nodes
     * @return string
     */
    public function renderViewTree($nodes)
    {
        $rows = [];
        $this->flattenTree($nodes, $rows, 0);

        $html = '<table class="table table-condensed table-bordered table-striped table-hover">';
        $html .= '<thead><tr><th style="width:15%">Type</th><th>View <small class="text-muted font-weight-normal">(click to copy)</small></th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $row['depth']);
            $count = $row['count'] > 1 ? ' (' . $row['count'] . ')' : '';
            $html .= '<tr>';
            $html .= '<td>' . Html::encode(ucfirst($row['type'])) . '</td>';
            $html .= '<td>'
                . '<code class="copyable-value" style="cursor:pointer">' . $indent . Html::encode($row['short']) . $count . '</code>'
                . ' ' . Html::tag('span', 'Copied!', ['class' => 'copyable-status text-success font-weight-bold', 'hidden' => true])
                . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * @param array $nodes
     * @param list<array{short: string, type: string, depth: int, count: int}> $rows
     * @param int $depth
     */
    private function flattenTree($nodes, &$rows, $depth)
    {
        $grouped = $this->groupNodes($nodes);
        foreach ($grouped as $item) {
            $rows[] = [
                'short' => $item['node']['short'],
                'type' => $this->classifyView($item['node']['short']),
                'depth' => $depth,
                'count' => $item['count'],
            ];
            if (!empty($item['children'])) {
                $this->flattenTree($item['children'], $rows, $depth + 1);
            }
        }
    }

    /**
     * Builds a plain text representation of the collected data for clipboard copy.
     *
     * @return string
     */
    public function buildPlainText()
    {
        $data = $this->data;
        $lines = [];

        $lines[] = 'Route: ' . ($data['route'] ?? '');
        if ($data['controllerClass'] !== null) {
            $actionInfo = $data['controllerClass'] . '::' . ($data['actionMethod'] ?? '') . '()';
            if ($data['actionLine'] !== null) {
                $actionInfo .= ' (line ' . $data['actionLine'] . ')';
            }
            $lines[] = 'Controller: ' . $actionInfo;
        }
        if ($data['layout'] !== null) {
            $lines[] = 'Layout: ' . $data['layout'];
        }

        $routeParams = $data['routeParams'] ?? [];
        if (!empty($routeParams)) {
            $lines[] = 'Route Params: ' . http_build_query($routeParams);
        }

        $viewTree = $data['viewTree'] ?? [];
        if (!empty($viewTree)) {
            $lines[] = 'Views:';
            $this->buildPlainTreeLines($viewTree, $lines, '');
        }

        $behaviors = $data['behaviors'] ?? [];
        if (!empty($behaviors)) {
            $names = array_map(function ($b) {
                $parts = explode('\\', $b['class']);
                return end($parts);
            }, $behaviors);
            $lines[] = 'Behaviors: ' . implode(', ', $names);
        }

        return implode("\n", $lines);
    }

    /**
     * @return string|null
     */
    private function resolveControllerFile()
    {
        $controller = Yii::$app->controller;
        if ($controller === null) {
            return null;
        }

        try {
            $reflector = new \ReflectionClass(get_class($controller));
            $fileName = $reflector->getFileName();
            return $fileName !== false ? $this->shortenPath($fileName) : null;
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    private function resolveActionMethod()
    {
        $action = Yii::$app->requestedAction;
        if ($action instanceof InlineAction) {
            return $action->actionMethod;
        }
        if ($action !== null) {
            return get_class($action) . '::run()';
        }
        return null;
    }

    /**
     * @return int|null
     */
    private function resolveActionLine()
    {
        $controller = Yii::$app->controller;
        $action = Yii::$app->requestedAction;
        if ($controller === null || !$action instanceof InlineAction) {
            return null;
        }
        try {
            $method = new \ReflectionMethod($controller, $action->actionMethod);
            $line = $method->getStartLine();
            return $line !== false ? $line : null;
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    private function resolveLayout()
    {
        $controller = Yii::$app->controller;
        if ($controller === null) {
            return null;
        }
        $layout = $controller->findLayoutFile($controller->getView());
        if ($layout === false) {
            return null;
        }
        return $this->shortenPath($layout);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRouteParams()
    {
        $request = Yii::$app->getRequest();
        if (!$request instanceof \yii\web\Request) {
            return [];
        }
        return $request->getQueryParams();
    }

    /**
     * @return list<array{name: string, class: string}>
     */
    private function resolveBehaviors()
    {
        $controller = Yii::$app->controller;
        if ($controller === null) {
            return [];
        }

        $result = [];
        foreach ($controller->getBehaviors() as $name => $behavior) {
            $result[] = [
                'name' => (string) $name,
                'class' => get_class($behavior),
            ];
        }
        return $result;
    }

    /**
     * @param string $path
     * @return string
     */
    private function shortenPath($path)
    {
        $appPath = Yii::getAlias('@app', false);
        if (is_string($appPath) && strpos($path, $appPath) === 0) {
            return ltrim(substr($path, strlen($appPath)), '/\\');
        }

        $aliases = ['@vendor', '@runtime'];
        foreach ($aliases as $alias) {
            $aliasPath = Yii::getAlias($alias, false);
            if (is_string($aliasPath) && strpos($path, $aliasPath) === 0) {
                return $alias . substr($path, strlen($aliasPath));
            }
        }
        return $path;
    }

    /**
     * @param string $path
     * @return string one of: layout, widget, partial, view
     */
    private function classifyView($path)
    {
        if (strpos($path, 'layouts/') !== false) {
            return 'layout';
        }
        if (strpos($path, 'widgets/') !== false || strpos($path, 'widget/') !== false) {
            return 'widget';
        }
        if (strpos(basename($path), '_') === 0) {
            return 'partial';
        }
        return 'view';
    }

    /**
     * @param string $type
     * @return string
     */

    /**
     * @param array $nodes
     * @param list<string> $lines
     * @param string $prefix
     */
    private function buildPlainTreeLines($nodes, &$lines, $prefix)
    {
        $grouped = $this->groupNodes($nodes);
        foreach ($grouped as $item) {
            $countSuffix = $item['count'] > 1 ? ' (' . $item['count'] . ')' : '';
            $lines[] = $prefix . '  - ' . $item['node']['short'] . $countSuffix;
            if (!empty($item['children'])) {
                $this->buildPlainTreeLines($item['children'], $lines, $prefix . '    ');
            }
        }
    }

    /**
     * Groups consecutive nodes with the same short path and merges their children.
     *
     * @param array $nodes
     * @return list<array{node: array, count: int, children: array}>
     */
    private function groupNodes($nodes)
    {
        $grouped = [];
        foreach ($nodes as $node) {
            $last = end($grouped);
            if ($last !== false && $last['node']['short'] === $node['short']) {
                $grouped[key($grouped)]['count']++;
                $grouped[key($grouped)]['children'] = array_merge(
                    $grouped[key($grouped)]['children'],
                    isset($node['children']) ? $node['children'] : []
                );
            } else {
                $grouped[] = [
                    'node' => $node,
                    'count' => 1,
                    'children' => isset($node['children']) ? $node['children'] : [],
                ];
            }
        }
        return $grouped;
    }
}
