<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Yii;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\View;
use yii\base\ViewEvent;
use yii\debug\Panel;

/**
 * Debugger panel that collects and displays request context:
 * controller/action, layout, rendered views tree and applied behaviors.
 *
 * @phpstan-type ViewNode array{file: string, short: string, children: list<array<string, mixed>>}
 * @phpstan-type GroupedViewNode array{node: ViewNode, count: int, children: list<array<string, mixed>>}
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
     * @var list<ViewNode>
     */
    private $_viewTree = [];

    /**
     * @var list<ViewNode>
     */
    private $_renderStack = [];

    /**
     * @var RequestContextTreeFormatter|null
     */
    private $_treeFormatter;

    /**
     * @var RequestContextTextFormatter|null
     */
    private $_textFormatter;

    /**
     * @var RequestContextValueRenderer|null
     */
    private $_valueRenderer;

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
        if (Yii::$app === null) {
            return '';
        }

        return Yii::$app->view->render('panels/requestContext/summary', ['panel' => $this]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        if (Yii::$app === null) {
            return '';
        }

        return Yii::$app->view->render('panels/requestContext/detail', ['panel' => $this]);
    }

    /**
     * @param string $value
     * @return string
     */
    public function renderCopyableValue($value)
    {
        return $this->getValueRenderer()->renderCopyableValue($value);
    }

    /**
     * @return array<string, string|int|null>
     */
    public function getContextRows()
    {
        $data = $this->data;
        $rows = [
            'Route' => $this->renderCopyableValue((string) ($data['route'] ?? '')),
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
        if (Yii::$app === null) {
            return [];
        }

        $controller = Yii::$app->controller;
        $action = Yii::$app->requestedAction;

        return [
            'controllerClass' => $controller !== null ? get_class($controller) : null,
            'controllerFile' => $this->resolveControllerFile($controller),
            'actionId' => $controller !== null && $controller->action !== null ? $controller->action->id : null,
            'actionMethod' => $this->resolveActionMethod($action),
            'actionLine' => $this->resolveActionLine($controller, $action),
            'layout' => $this->resolveLayout($controller),
            'route' => $action !== null ? $action->getUniqueId() : Yii::$app->requestedRoute,
            'routeParams' => $this->resolveRouteParams(),
            'behaviors' => $this->resolveBehaviors($controller),
            'viewTree' => $this->_viewTree,
            'viewCount' => count($this->_renderedViews),
        ];
    }

    /**
     * Renders the view tree as nested HTML list.
     *
     * @param list<array<string, mixed>> $nodes
     * @return string
     */
    public function renderViewTree($nodes)
    {
        return $this->getTreeFormatter()->renderViewTree($nodes);
    }

    /**
     * Builds a plain text representation of the collected data for clipboard copy.
     *
     * @return string
     */
    public function buildPlainText()
    {
        return $this->getTextFormatter()->buildPlainText($this->data);
    }

    /**
     * @param \yii\base\Controller|null $controller
     * @return string|null
     */
    private function resolveControllerFile($controller)
    {
        if ($controller === null) {
            return null;
        }

        try {
            $reflector = new ReflectionClass(get_class($controller));
            $fileName = $reflector->getFileName();
            return $fileName !== false ? $this->shortenPath($fileName) : null;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param \yii\base\Action|null $action
     * @return string|null
     */
    private function resolveActionMethod($action)
    {
        if ($action instanceof InlineAction) {
            return $action->actionMethod;
        }
        if ($action !== null) {
            return get_class($action) . '::run()';
        }
        return null;
    }

    /**
     * @param \yii\base\Controller|null $controller
     * @param \yii\base\Action|null $action
     * @return int|null
     */
    private function resolveActionLine($controller, $action)
    {
        if ($controller === null || !$action instanceof InlineAction) {
            return null;
        }
        try {
            $method = new ReflectionMethod($controller, $action->actionMethod);
            $line = $method->getStartLine();
            return $line !== false ? $line : null;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param \yii\base\Controller|null $controller
     * @return string|null
     */
    private function resolveLayout($controller)
    {
        if ($controller === null) {
            return null;
        }
        $layout = $controller->findLayoutFile($controller->getView());
        if (!is_string($layout)) {
            return null;
        }
        return $this->shortenPath($layout);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRouteParams()
    {
        $requestedParams = Yii::$app !== null ? Yii::$app->requestedParams : null;
        if (!is_array($requestedParams)) {
            return [];
        }

        return $requestedParams;
    }

    /**
     * @param \yii\base\Controller|null $controller
     * @return list<array{name: string, class: string}>
     */
    private function resolveBehaviors($controller)
    {
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
     * @return RequestContextTreeFormatter
     */
    private function getTreeFormatter()
    {
        if ($this->_treeFormatter === null) {
            $this->_treeFormatter = new RequestContextTreeFormatter($this->getValueRenderer());
        }

        return $this->_treeFormatter;
    }

    /**
     * @return RequestContextTextFormatter
     */
    private function getTextFormatter()
    {
        if ($this->_textFormatter === null) {
            $this->_textFormatter = new RequestContextTextFormatter($this->getTreeFormatter());
        }

        return $this->_textFormatter;
    }

    /**
     * @return RequestContextValueRenderer
     */
    private function getValueRenderer()
    {
        if ($this->_valueRenderer === null) {
            $this->_valueRenderer = new RequestContextValueRenderer();
        }

        return $this->_valueRenderer;
    }
}
