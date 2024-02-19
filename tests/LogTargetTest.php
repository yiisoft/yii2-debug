<?php

namespace yiiunit\debug;

use Yii;
use yii\debug\LogTarget;
use yii\debug\Module;
use yii\log\Logger;

class LogTargetTest extends TestCase
{
    public function testGetRequestTime()
    {
        Yii::$app->getRequest()->setUrl('dummy');

        $module = new Module('debug');
        $module->bootstrap(Yii::$app);

        $logTarget = new LogTarget($module);
        $data = $this->invoke($logTarget, 'collectSummary');
        self::assertSame($_SERVER['REQUEST_TIME_FLOAT'], $data['time']);
    }

    public function testLogPanelClosures()
    {
        Yii::$app->getRequest()->setUrl('dummy');
        $module = new Module('debug');
        $module->bootstrap(Yii::$app);
        $logTarget = $module->logTarget;

        // Logs to test
        Yii::debug("qwe");
        Yii::warning("asd");
        Yii::info(['test_callback' => function($cbArg) {
            return $cbArg . 'cbResult';
        }]);

        Yii::$app->log->getLogger()->flush(true);
        $manifest = $logTarget->loadManifest();
        $lastLogEntry = reset($manifest);
        $this->assertNotEmpty($lastLogEntry);
        $logTarget->loadTagToPanels($lastLogEntry['tag']);
        $panelData = $module->panels['log']->data;

        // Actual tests
        $this->assertArrayHasKey('messages', $panelData);

        $this->assertEquals('qwe', $panelData['messages'][0][0]);
        $this->assertEquals(Logger::LEVEL_TRACE, $panelData['messages'][0][1]);

        $this->assertEquals('asd', $panelData['messages'][1][0]);
        $this->assertEquals(Logger::LEVEL_WARNING, $panelData['messages'][1][1]);

        $this->assertStringContainsString('test_callback', $panelData['messages'][2][0]);
        $this->assertStringContainsString('function($cbArg)', $panelData['messages'][2][0]);
        $this->assertStringContainsString("return \$cbArg . 'cbResult'", $panelData['messages'][2][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $panelData['messages'][2][1]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }
}
