<?php

namespace yiiunit\extensions\debug;

use Yii;
use yii\debug\Module;
use yii\debug\Panel;

class PanelTest extends TestCase
{
    public function testTraceLink_DefaultText()
    {
        $panel = new Panel(['module' => new Module('debug')]);
        $this->assertEquals('file:10', $panel->traceLink(['file' => 'file', 'line' => 10]));
    }

    public function testTraceLink_CustomText()
    {
        $panel = new Panel(['module' => new Module('debug')]);
        $this->assertEquals('text', $panel->traceLink(['file' => 'file', 'line' => 10, 'text' => 'text']));
    }

    public function testTraceLink_TraceLinkByText()
    {
        $panel = new Panel(['module' => new Module('debug')]);
        $panel->module->traceLink = Module::TRACELINK_PHPSTORM;
        $this->assertEquals('<a href="phpstorm://open?url=file://path/to/my/file.php&line=10">path/to/my/file.php:10</a>', $panel->traceLink(['file' => 'path/to/my/file.php', 'line' => 10]));
    }

    public function testTraceLink_TraceLinkByCallback()
    {
        $panel = new Panel(['module' => new Module('debug')]);
        $expected = 'http://my.custom.link';
        $panel->module->traceLink = function () use ($expected) {
            return $expected;
        };
        $this->assertEquals($expected, $panel->traceLink(['file' => 'file', 'line' => 10]));

        $panel->module->traceLink = function () use ($expected) {
            return 'phpstorm://open?url={file}&line={line}';
        };
        $this->assertEquals('phpstorm://open?url=file&line=10', $panel->traceLink(['file' => 'file', 'line' => 10]));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }
}