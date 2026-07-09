<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

class RequestContextTextFormatter
{
    /**
     * @var RequestContextTreeFormatter
     */
    private $treeFormatter;

    /**
     * @param RequestContextTreeFormatter $treeFormatter
     */
    public function __construct($treeFormatter)
    {
        $this->treeFormatter = $treeFormatter;
    }

    /**
     * @param array<string, mixed> $data
     * @return string
     */
    public function buildPlainText(array $data)
    {
        $lines = [];

        $lines[] = 'Route: ' . ($data['route'] ?? '');
        $actionInfo = $this->buildActionInfo($data);
        if ($actionInfo !== null) {
            $lines[] = 'Controller: ' . $actionInfo;
        }
        if (($data['layout'] ?? null) !== null) {
            $lines[] = 'Layout: ' . $data['layout'];
        }

        $routeParams = $data['routeParams'] ?? [];
        if (!empty($routeParams)) {
            $lines[] = 'Route Params: ' . http_build_query($routeParams);
        }

        $viewTree = $data['viewTree'] ?? [];
        if (!empty($viewTree)) {
            $lines[] = 'Views:';
            foreach ($this->treeFormatter->buildPlainLines($viewTree) as $line) {
                $lines[] = $line;
            }
        }

        $behaviors = $data['behaviors'] ?? [];
        if (!empty($behaviors)) {
            $names = array_map(function ($behavior) {
                $parts = explode('\\', $behavior['class']);

                return end($parts);
            }, $behaviors);
            $lines[] = 'Behaviors: ' . implode(', ', $names);
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $data
     * @return string|null
     */
    private function buildActionInfo(array $data)
    {
        $controllerClass = isset($data['controllerClass']) && is_string($data['controllerClass']) ? $data['controllerClass'] : null;
        $actionMethod = isset($data['actionMethod']) && is_string($data['actionMethod']) ? $data['actionMethod'] : null;

        if ($controllerClass === null && $actionMethod === null) {
            return null;
        }

        if ($actionMethod === null) {
            $actionInfo = $controllerClass;
        } elseif (strpos($actionMethod, '::') !== false) {
            $actionInfo = $actionMethod;
        } elseif ($controllerClass !== null) {
            $actionInfo = $controllerClass . '::' . $actionMethod . '()';
        } else {
            $actionInfo = $actionMethod . '()';
        }

        if (($data['actionLine'] ?? null) !== null) {
            $actionInfo .= ' (line ' . $data['actionLine'] . ')';
        }

        return $actionInfo;
    }
}
