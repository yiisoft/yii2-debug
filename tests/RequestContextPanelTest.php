<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\debug;

use Yii;
use yii\base\Action;
use yii\base\Event;
use yii\base\InlineAction;
use yii\base\View;
use yii\base\ViewEvent;
use yii\debug\Module;
use yii\debug\panels\RequestContextPanel;
use yii\filters\ContentNegotiator;
use yiiunit\debug\support\TestController;

class RequestContextPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    private function getPanel(): RequestContextPanel
    {
        return new RequestContextPanel(['module' => new Module('debug')]);
    }

    public function testGetName(): void
    {
        $panel = $this->getPanel();
        $this->assertSame('Context', $panel->getName());
    }

    /**
     * @dataProvider saveKeysProvider
     */
    public function testSaveReturnsExpectedKey(string $key): void
    {
        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertArrayHasKey($key, $data);
    }

    /**
     * @return array<int, array{string}>
     */
    public function saveKeysProvider(): array
    {
        return [
            ['controllerClass'],
            ['controllerFile'],
            ['actionId'],
            ['actionMethod'],
            ['actionLine'],
            ['layout'],
            ['route'],
            ['routeParams'],
            ['behaviors'],
            ['viewTree'],
            ['viewCount'],
        ];
    }

    public function testSaveWithNoController(): void
    {
        Yii::$app->controller = null;
        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertNull($data['controllerClass']);
        $this->assertNull($data['controllerFile']);
        $this->assertNull($data['actionId']);
        $this->assertNull($data['actionMethod']);
        $this->assertNull($data['actionLine']);
        $this->assertNull($data['layout']);
        $this->assertEmpty($data['behaviors']);
    }

    public function testSaveWithController(): void
    {
        $controller = new TestController('test', Yii::$app);
        $action = new InlineAction('index', $controller, 'actionIndex');
        $controller->action = $action;
        Yii::$app->controller = $controller;
        Yii::$app->requestedAction = $action;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertSame(TestController::class, $data['controllerClass']);
        $this->assertNotNull($data['controllerFile']);
        $this->assertSame('index', $data['actionId']);
        $this->assertSame('actionIndex', $data['actionMethod']);
        $this->assertIsArray($data['behaviors']);
        $this->assertSame('test/index', $data['route']);
    }

    public function testSaveWithControllerBehaviors(): void
    {
        $controller = new TestController('test', Yii::$app);
        $controller->attachBehavior('testBehavior', new ContentNegotiator());
        Yii::$app->controller = $controller;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertNotEmpty($data['behaviors']);
        $this->assertSame('testBehavior', $data['behaviors'][0]['name']);
        $this->assertSame(ContentNegotiator::class, $data['behaviors'][0]['class']);
    }

    public function testSaveRouteParams(): void
    {
        $_GET = ['id' => '42', 'slug' => 'test'];
        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertSame(['id' => '42', 'slug' => 'test'], $data['routeParams']);
        $_GET = [];
    }

    public function testSaveActionLineWithInlineAction(): void
    {
        $controller = new TestController('test', Yii::$app);
        $action = new InlineAction('index', $controller, 'actionIndex');
        $controller->action = $action;
        Yii::$app->controller = $controller;
        Yii::$app->requestedAction = $action;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertIsInt($data['actionLine']);
        $this->assertGreaterThan(0, $data['actionLine']);
    }

    public function testSaveActionLineNullWithoutInlineAction(): void
    {
        Yii::$app->controller = null;
        Yii::$app->requestedAction = null;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertNull($data['actionLine']);
    }

    public function testRenderViewTreeEmpty(): void
    {
        $panel = $this->getPanel();
        $html = $panel->renderViewTree([]);

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('</table>', $html);
    }

    public function testRenderViewTreeWithNodes(): void
    {
        $panel = $this->getPanel();
        $nodes = [
            ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
            ['file' => '/app/views/site/about.php', 'short' => 'views/site/about.php', 'children' => []],
        ];
        $html = $panel->renderViewTree($nodes);

        $this->assertStringContainsString('index.php', $html);
        $this->assertStringContainsString('about.php', $html);
        $this->assertStringContainsString('View', $html);
    }

    public function testRenderViewTreeGroupsDuplicates(): void
    {
        $panel = $this->getPanel();
        $nodes = [
            ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
            ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
            ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
        ];
        $html = $panel->renderViewTree($nodes);

        $this->assertStringContainsString('(3)', $html);
    }

    /**
     * @dataProvider classifyViewProvider
     */
    public function testRenderViewTreeClassifiesType(string $short, string $expectedType): void
    {
        $panel = $this->getPanel();
        $nodes = [
            ['file' => '/app/' . $short, 'short' => $short, 'children' => []],
        ];
        $html = $panel->renderViewTree($nodes);

        $this->assertStringContainsString($expectedType, $html);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function classifyViewProvider(): array
    {
        return [
            'layout' => ['views/layouts/main.php', 'Layout'],
            'widgets dir' => ['views/widgets/menu.php', 'Widget'],
            'widget dir' => ['views/widget/nav.php', 'Widget'],
            'partial' => ['views/site/_form.php', 'Partial'],
            'view' => ['views/site/index.php', 'View'],
        ];
    }

    public function testRenderViewTreeWithNestedChildren(): void
    {
        $panel = $this->getPanel();
        $nodes = [
            [
                'file' => '/app/views/layouts/main.php',
                'short' => 'views/layouts/main.php',
                'children' => [
                    ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
                ],
            ],
        ];
        $html = $panel->renderViewTree($nodes);

        $this->assertStringContainsString('main.php', $html);
        $this->assertStringContainsString('index.php', $html);
        $this->assertStringContainsString('&nbsp;', $html);
    }

    public function testBuildPlainText(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => 'app\controllers\SiteController',
            'actionMethod' => 'actionIndex',
            'actionLine' => 25,
            'layout' => 'views/layouts/main.php',
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => [],
            'controllerFile' => null,
            'viewCount' => 0,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringContainsString('Route: site/index', $text);
        $this->assertStringContainsString('SiteController::actionIndex()', $text);
        $this->assertStringContainsString('line 25', $text);
        $this->assertStringContainsString('Layout: views/layouts/main.php', $text);
    }

    public function testBuildPlainTextWithoutActionLine(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => 'app\controllers\SiteController',
            'actionMethod' => 'actionIndex',
            'actionLine' => null,
            'layout' => null,
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => [],
            'controllerFile' => null,
            'viewCount' => 0,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringNotContainsString('line', $text);
        $this->assertStringNotContainsString('Layout:', $text);
    }

    public function testBuildPlainTextWithRouteParams(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/view',
            'controllerClass' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => ['id' => '42', 'slug' => 'test'],
            'controllerFile' => null,
            'viewCount' => 0,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringContainsString('Route Params:', $text);
        $this->assertStringContainsString('id=42', $text);
        $this->assertStringContainsString('slug=test', $text);
    }

    public function testBuildPlainTextWithViewTree(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewTree' => [
                [
                    'file' => '/app/views/site/index.php',
                    'short' => 'views/site/index.php',
                    'children' => [
                        ['file' => '/app/views/site/_item.php', 'short' => 'views/site/_item.php', 'children' => []],
                    ],
                ],
            ],
            'behaviors' => [],
            'routeParams' => [],
            'controllerFile' => null,
            'viewCount' => 2,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringContainsString('Views:', $text);
        $this->assertStringContainsString('views/site/index.php', $text);
        $this->assertStringContainsString('views/site/_item.php', $text);
    }

    public function testBuildPlainTextWithBehaviors(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewTree' => [],
            'behaviors' => [
                ['name' => 'access', 'class' => 'yii\filters\AccessControl'],
            ],
            'routeParams' => [],
            'controllerFile' => null,
            'viewCount' => 0,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringContainsString('Behaviors: AccessControl', $text);
    }

    public function testBuildPlainTextWithDuplicateViews(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewTree' => [
                ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
                ['file' => '/app/views/site/index.php', 'short' => 'views/site/index.php', 'children' => []],
            ],
            'behaviors' => [],
            'routeParams' => [],
            'controllerFile' => null,
            'viewCount' => 2,
        ];
        $text = $panel->buildPlainText();

        $this->assertStringContainsString('(2)', $text);
    }

    public function testGetContextRows(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => 'app\controllers\SiteController',
            'controllerFile' => 'controllers/SiteController.php',
            'actionMethod' => 'actionIndex',
            'actionLine' => 10,
            'layout' => 'views/layouts/main.php',
            'viewCount' => 3,
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => [],
        ];
        $rows = $panel->getContextRows();

        $this->assertArrayHasKey('Route', $rows);
        $this->assertArrayHasKey('Controller', $rows);
        $this->assertArrayHasKey('Controller File', $rows);
        $this->assertArrayHasKey('Action', $rows);
        $this->assertArrayHasKey('Action Line', $rows);
        $this->assertArrayHasKey('Layout', $rows);
        $this->assertArrayHasKey('Views Rendered', $rows);
    }

    public function testGetContextRowsFiltersNulls(): void
    {
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => null,
            'controllerFile' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewCount' => 0,
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => [],
        ];
        $rows = $panel->getContextRows();

        $this->assertArrayHasKey('Route', $rows);
        $this->assertArrayNotHasKey('Controller', $rows);
        $this->assertArrayNotHasKey('Controller File', $rows);
        $this->assertArrayNotHasKey('Action', $rows);
        $this->assertArrayNotHasKey('Action Line', $rows);
        $this->assertArrayNotHasKey('Layout', $rows);
    }

    public function testRenderCopyableValue(): void
    {
        $panel = $this->getPanel();
        $html = $panel->renderCopyableValue('test-value');

        $this->assertStringContainsString('test-value', $html);
        $this->assertStringContainsString('copyable-value', $html);
        $this->assertStringContainsString('Copied!', $html);
    }

    public function testRenderCopyableValueEscapesHtml(): void
    {
        $panel = $this->getPanel();
        $html = $panel->renderCopyableValue('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testShortenPathWithAppAlias(): void
    {
        $panel = $this->getPanel();
        $appPath = Yii::getAlias('@app');
        $result = $this->invoke($panel, 'shortenPath', [$appPath . '/controllers/SiteController.php']);

        $this->assertSame('controllers/SiteController.php', $result);
    }

    public function testShortenPathWithVendorAlias(): void
    {
        $panel = $this->getPanel();
        $vendorPath = Yii::getAlias('@vendor');
        $result = $this->invoke($panel, 'shortenPath', [$vendorPath . '/yiisoft/yii2/BaseYii.php']);

        $this->assertSame('@vendor/yiisoft/yii2/BaseYii.php', $result);
    }

    public function testShortenPathWithRuntimeAlias(): void
    {
        Yii::setAlias('@runtime', '/tmp/test-runtime');
        $panel = $this->getPanel();
        $result = $this->invoke($panel, 'shortenPath', ['/tmp/test-runtime/logs/app.log']);

        $this->assertSame('@runtime/logs/app.log', $result);
    }

    public function testShortenPathUnknownReturnsAsIs(): void
    {
        $panel = $this->getPanel();
        $result = $this->invoke($panel, 'shortenPath', ['/some/random/path.php']);

        $this->assertSame('/some/random/path.php', $result);
    }

    /**
     * @dataProvider classifyViewDataProvider
     */
    public function testClassifyView(string $path, string $expected): void
    {
        $panel = $this->getPanel();
        $this->assertSame($expected, $this->invoke($panel, 'classifyView', [$path]));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function classifyViewDataProvider(): array
    {
        return [
            'layout' => ['views/layouts/main.php', 'layout'],
            'widgets dir' => ['views/widgets/menu.php', 'widget'],
            'widget dir' => ['views/widget/nav.php', 'widget'],
            'partial' => ['views/site/_form.php', 'partial'],
            'view' => ['views/site/index.php', 'view'],
        ];
    }

    public function testGroupNodesConsecutive(): void
    {
        $panel = $this->getPanel();
        $nodes = [
            ['short' => 'a.php', 'children' => [['short' => 'child1.php', 'children' => []]]],
            ['short' => 'a.php', 'children' => [['short' => 'child2.php', 'children' => []]]],
            ['short' => 'b.php', 'children' => []],
        ];
        $result = $this->invoke($panel, 'groupNodes', [$nodes]);

        $this->assertCount(2, $result);
        $this->assertSame(2, $result[0]['count']);
        $this->assertCount(2, $result[0]['children']);
        $this->assertSame(1, $result[1]['count']);
    }

    public function testGroupNodesNonConsecutiveSameNotGrouped(): void
    {
        $panel = $this->getPanel();
        $nodes = [
            ['short' => 'a.php', 'children' => []],
            ['short' => 'b.php', 'children' => []],
            ['short' => 'a.php', 'children' => []],
        ];
        $result = $this->invoke($panel, 'groupNodes', [$nodes]);

        $this->assertCount(3, $result);
    }

    public function testInitTracksViewEvents(): void
    {
        Event::offAll();
        $panel = $this->getPanel();

        $view = new View();
        $viewFile = '/app/views/site/index.php';

        $event = new ViewEvent(['viewFile' => $viewFile]);
        $view->trigger(View::EVENT_BEFORE_RENDER, $event);
        $view->trigger(View::EVENT_AFTER_RENDER, $event);

        $data = $panel->save();
        $this->assertSame(1, $data['viewCount']);
        $this->assertCount(1, $data['viewTree']);
        $this->assertSame($viewFile, $data['viewTree'][0]['file']);
    }

    public function testInitTracksNestedViews(): void
    {
        Event::offAll();
        $panel = $this->getPanel();

        $view = new View();
        $parentFile = '/app/views/layouts/main.php';
        $childFile = '/app/views/site/index.php';

        $view->trigger(View::EVENT_BEFORE_RENDER, new ViewEvent(['viewFile' => $parentFile]));
        $view->trigger(View::EVENT_BEFORE_RENDER, new ViewEvent(['viewFile' => $childFile]));
        $view->trigger(View::EVENT_AFTER_RENDER, new ViewEvent(['viewFile' => $childFile]));
        $view->trigger(View::EVENT_AFTER_RENDER, new ViewEvent(['viewFile' => $parentFile]));

        $data = $panel->save();
        $this->assertSame(2, $data['viewCount']);
        $this->assertCount(1, $data['viewTree']);
        $this->assertCount(1, $data['viewTree'][0]['children']);
    }

    public function testSummaryView(): void
    {
        $viewPath = dirname(__DIR__) . '/src/views/default';
        $panel = $this->getPanel();
        $panel->data = ['route' => 'site/index'];
        $summary = Yii::$app->view->renderFile($viewPath . '/panels/requestContext/summary.php', ['panel' => $panel]);

        $this->assertStringContainsString('Context', $summary);
        $this->assertStringContainsString('&#9881;', $summary);
    }

    public function testDetailView(): void
    {
        $viewPath = dirname(__DIR__) . '/src/views/default';
        $panel = $this->getPanel();
        $panel->data = [
            'route' => 'site/index',
            'controllerClass' => null,
            'controllerFile' => null,
            'actionMethod' => null,
            'actionLine' => null,
            'layout' => null,
            'viewCount' => 0,
            'viewTree' => [],
            'behaviors' => [],
            'routeParams' => [],
        ];
        $detail = Yii::$app->view->renderFile($viewPath . '/panels/requestContext/detail.php', ['panel' => $panel]);

        $this->assertStringContainsString('Request Context', $detail);
        $this->assertStringContainsString('site/index', $detail);
    }

    public function testResolveLayoutWithControllerLayoutFalse(): void
    {
        $controller = new TestController('test', Yii::$app);
        $controller->layout = false;
        Yii::$app->controller = $controller;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertNull($data['layout']);
    }

    public function testSaveWithStandaloneAction(): void
    {
        $controller = new TestController('test', Yii::$app);
        $action = new Action('external', $controller);
        Yii::$app->requestedAction = $action;
        Yii::$app->controller = $controller;

        $panel = $this->getPanel();
        $data = $panel->save();

        $this->assertStringContainsString('::run()', $data['actionMethod']);
        $this->assertNull($data['actionLine']);
    }

    public function testSaveRouteParamsWithConsoleApp(): void
    {
        $this->destroyApplication();
        $this->mockApplication();

        $panel = new RequestContextPanel(['module' => new Module('debug')]);
        $data = $panel->save();

        $this->assertEmpty($data['routeParams']);
    }

    public function testInitAfterRenderWithEmptyStack(): void
    {
        Event::offAll();
        $panel = $this->getPanel();

        $view = new View();
        $view->trigger(View::EVENT_AFTER_RENDER, new ViewEvent(['viewFile' => '/test.php']));

        $data = $panel->save();
        $this->assertSame(1, $data['viewCount']);
        $this->assertEmpty($data['viewTree']);
    }
}
