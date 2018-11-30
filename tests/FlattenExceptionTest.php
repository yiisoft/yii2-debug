<?php

namespace yiiunit\debug;

use yii\debug\FlattenException;
use yii\web\NotFoundHttpException;

/**
 * @author Dmitry Bashkarev <dmitry@bashkarev.com>
 */
class FlattenExceptionTest extends TestCase
{

    public function testMessage()
    {
        $flattened = new FlattenException(new \Exception('test'));
        $this->assertEquals('test', $flattened->getMessage());
    }

    public function testCode()
    {
        $flattened = new FlattenException(new \Exception('test', 100));
        $this->assertEquals(100, $flattened->getCode());
    }

    public function testFile()
    {
        $flattened = new FlattenException(new \Exception('test', 100));
        $this->assertEquals(__FILE__, $flattened->getFile());
    }

    public function testLine()
    {
        $flattened = new FlattenException(new \Exception('test', 100));
        $this->assertEquals(__LINE__ - 1, $flattened->getLine());
    }

    public function testTrace()
    {
        $i = (new \Exception('test'));
        $flattened = new FlattenException($i);

        $trace = $flattened->getTrace();
        $this->assertEquals(__NAMESPACE__, $trace[0]['namespace']);
        $this->assertEquals(__CLASS__, $trace[0]['class']);
        $this->assertEquals('FlattenExceptionTest', $trace[0]['short_class']);
        $this->assertEquals(__FUNCTION__, $trace[0]['function']);
    }

    public function testPrevious()
    {
        $exception2 = new \Exception;
        $exception = new \Exception('test', 0, $exception2);

        $flattened = new FlattenException($exception);
        $flattened2 = new FlattenException($exception2);

        $this->assertSame($flattened2->getTrace(), $flattened->getPrevious()->getTrace());
    }

    public function testTraceAsString()
    {
        $exception = $this->createException('test');
        $this->assertEquals($exception->getTraceAsString(), (new FlattenException($exception))->getTraceAsString());
    }

    public function testToString()
    {
        $exception = new \Exception();
        $this->assertEquals($exception->__toString(), (new FlattenException($exception))->__toString(), 'empty');
        $exception = new \Exception('test');
        $this->assertEquals($exception->__toString(), (new FlattenException($exception))->__toString());
    }

    public function testClass()
    {
        $this->assertEquals((new FlattenException(new \Exception()))->getClass(), 'Exception');
    }

    public function testArguments()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM skip');
        }
        $dh = opendir(__DIR__);
        $fh = tmpfile();

        $incomplete = unserialize('O:14:"BogusTestClass":0:{}');

        $exception = $this->createException([
            (object)['foo' => 1],
            new NotFoundHttpException(),
            $incomplete,
            $dh,
            $fh,
            function () {
            },
            [1, 2],
            ['foo' => 123],
            null,
            true,
            false,
            0,
            0.0,
            '0',
            '',
            INF,
            NAN,
        ]);

        $flattened = new FlattenException($exception);
        $trace = $flattened->getTrace();
        $args = $trace[0]['args'];
        $array = $args[0][1];

        closedir($dh);
        fclose($fh);

        $i = 0;
        $this->assertSame(['object', 'stdClass'], $array[$i++]);
        $this->assertSame(['object', 'yii\web\NotFoundHttpException'], $array[$i++]);
        $this->assertSame(['incomplete-object', 'BogusTestClass'], $array[$i++]);
        $this->assertSame(['resource', 'stream'], $array[$i++]);
        $this->assertSame(['resource', 'stream'], $array[$i++]);

        $args = $array[$i++];
        $this->assertSame($args[0], 'object');
        $this->assertTrue('Closure' === $args[1] || is_subclass_of($args[1], '\Closure'),
            'Expect object class name to be Closure or a subclass of Closure.');

        $this->assertSame(['array', [['integer', 1], ['integer', 2]]], $array[$i++]);
        $this->assertSame(['array', ['foo' => ['integer', 123]]], $array[$i++]);
        $this->assertSame(['null', null], $array[$i++]);
        $this->assertSame(['boolean', true], $array[$i++]);
        $this->assertSame(['boolean', false], $array[$i++]);
        $this->assertSame(['integer', 0], $array[$i++]);
        $this->assertSame(['float', 0.0], $array[$i++]);
        $this->assertSame(['string', '0'], $array[$i++]);
        $this->assertSame(['string', ''], $array[$i++]);
        $this->assertSame(['float', INF], $array[$i++]);

        // assertEquals() does not like NAN values.
        $this->assertEquals($array[$i][0], 'float');
        $this->assertTrue(is_nan($array[$i++][1]));
    }

    public function testClosureSerialize()
    {
        $exception = $this->createException(function () {
            return 1 + 1;
        });

        $flattened = new FlattenException($exception);
        $this->assertContains('Closure', serialize($flattened));
    }

    public function testRecursionInArguments()
    {
        $a = ['foo'];
        $a[] = [2, &$a];
        $exception = $this->createException($a);

        $flattened = new FlattenException($exception);
        $trace = $flattened->getTrace();
        $this->assertContains('*DEEP NESTED ARRAY*', serialize($trace));
    }

    public function testTooBigArray()
    {
        $a = [];
        for ($i = 0; $i < 20; ++$i) {
            for ($j = 0; $j < 50; ++$j) {
                for ($k = 0; $k < 10; ++$k) {
                    $a[$i][$j][$k] = 'value';
                }
            }
        }
        $a[20] = 'value';
        $a[21] = 'value1';
        $exception = $this->createException($a);

        $flattened = new FlattenException($exception);
        $trace = $flattened->getTrace();

        $this->assertSame($trace[0]['args'][0], ['array', ['array', '*SKIPPED over 10000 entries*']]);

        $serializeTrace = serialize($trace);

        $this->assertContains('*SKIPPED over 10000 entries*', $serializeTrace);
        $this->assertNotContains('*value1*', $serializeTrace);
    }

    private function createException($foo)
    {
        return new \Exception();
    }
}
