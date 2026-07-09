<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use yii\helpers\Html;

/**
 * @phpstan-type ViewNode array{file: string, short: string, children: list<array<string, mixed>>}
 * @phpstan-type GroupedViewNode array{node: ViewNode, count: int, children: list<array<string, mixed>>}
 */
class RequestContextTreeFormatter
{
    /**
     * @var RequestContextValueRenderer
     */
    private $valueRenderer;

    /**
     * @param RequestContextValueRenderer $valueRenderer
     */
    public function __construct($valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
    }

    /**
     * @param list<array<string, mixed>> $nodes
     * @return string
     */
    public function renderViewTree($nodes)
    {
        $rows = [];
        $this->flattenTree($nodes, $rows, 0);

        $html = '<table class="table table-condensed table-bordered table-striped table-hover">';
        $html .= '<thead><tr><th style="width:15%">Type</th><th>View <small class="text-muted font-weight-normal">(click to copy)</small></th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . Html::encode(ucfirst($row['type'])) . '</td>';
            $html .= '<td>' . $this->valueRenderer->renderIndentedCopyableValue($row['short'], $row['depth'], $row['count']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $nodes
     * @return list<string>
     */
    public function buildPlainLines($nodes)
    {
        $lines = [];
        $this->buildPlainTreeLines($nodes, $lines, '');

        return $lines;
    }

    /**
     * @param string $path
     * @return string one of: layout, widget, partial, view
     */
    public function classifyView($path)
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
     * Groups consecutive nodes with the same short path and merges their children.
     *
     * @param list<array<string, mixed>> $nodes
     * @return list<GroupedViewNode>
     */
    public function groupNodes($nodes)
    {
        $grouped = [];
        foreach ($nodes as $node) {
            if (!isset($node['file'], $node['short']) || !array_key_exists('children', $node) || !is_array($node['children'])) {
                continue;
            }

            /** @var ViewNode $viewNode */
            $viewNode = [
                'file' => (string) $node['file'],
                'short' => (string) $node['short'],
                'children' => $node['children'],
            ];

            $lastIndex = count($grouped) - 1;
            if ($lastIndex >= 0 && $grouped[$lastIndex]['node']['short'] === $viewNode['short']) {
                $grouped[$lastIndex]['count']++;
                $grouped[$lastIndex]['children'] = array_merge($grouped[$lastIndex]['children'], $viewNode['children']);
            } else {
                $grouped[] = [
                    'node' => $viewNode,
                    'count' => 1,
                    'children' => $viewNode['children'],
                ];
            }
        }

        return $grouped;
    }

    /**
     * @param list<array<string, mixed>> $nodes
     * @param list<array{short: string, type: string, depth: int, count: int}> $rows
     * @param int $depth
     * @return void
     */
    private function flattenTree($nodes, &$rows, $depth)
    {
        foreach ($this->groupNodes($nodes) as $item) {
            $rows[] = [
                'short' => (string) $item['node']['short'],
                'type' => $this->classifyView((string) $item['node']['short']),
                'depth' => $depth,
                'count' => $item['count'],
            ];
            if (!empty($item['children'])) {
                $this->flattenTree($item['children'], $rows, $depth + 1);
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $nodes
     * @param list<string> $lines
     * @param string $prefix
     * @return void
     */
    private function buildPlainTreeLines($nodes, &$lines, $prefix)
    {
        foreach ($this->groupNodes($nodes) as $item) {
            $countSuffix = $item['count'] > 1 ? ' (' . $item['count'] . ')' : '';
            $lines[] = $prefix . '  - ' . (string) $item['node']['short'] . $countSuffix;
            if (!empty($item['children'])) {
                $this->buildPlainTreeLines($item['children'], $lines, $prefix . '    ');
            }
        }
    }
}
