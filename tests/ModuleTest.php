<?php

namespace yiiunit\extensions\debug;

use yii\debug\Module;

class ModuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    // Tests :

    /**
     * Data provider for [[testCheckAccess()]]
     * @return array test data
     */
    public function dataProviderCheckAccess()
    {
        return [
            [
                [],
                '10.20.30.40',
                false
            ],
            [
                ['10.20.30.40'],
                '10.20.30.40',
                true
            ],
            [
                ['*'],
                '10.20.30.40',
                true
            ],
            [
                ['10.20.30.*'],
                '10.20.30.40',
                true
            ],
            [
                ['10.20.30.*'],
                '10.20.40.40',
                false
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCheckAccess
     *
     * @param array $allowedIPs
     * @param string $userIp
     * @param boolean $expectedResult
     */
    public function testCheckAccess(array $allowedIPs, $userIp, $expectedResult)
    {
        $module = new Module('debug');
        $module->allowedIPs = $allowedIPs;
        $_SERVER['REMOTE_ADDR'] = $userIp;
        $this->assertEquals($expectedResult, $this->invoke($module, 'checkAccess'));
    }
} 