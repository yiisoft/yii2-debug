<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\base\Object;
use yii\web\HttpException;

/**
 * FlattenException wraps a PHP Exception to be able to serialize it.
 * Basically, this class removes all objects from the trace.
 * Ported from Symfony components
 *
 * @author Dmitry Bashkarev <dmitry@bashkarev.com>
 * @since 2.0.10
 */
class FlattenException extends Object
{
    private $_message;
    private $_code;
    private $_previous;
    private $_trace;
    private $_class;
    private $_statusCode;
    private $_file;
    private $_line;

    public static function create(\Exception $exception, $statusCode = null)
    {
        $e = new static();
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpException) {
            $statusCode = $exception->statusCode;
        }

        if (null === $statusCode) {
            $statusCode = 500;
        }

        $e->setStatusCode($statusCode);
        $e->setTraceFromException($exception);
        $e->setClass(get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());

        $previous = $exception->getPrevious();

        if ($previous instanceof \Exception) {
            $e->setPrevious(static::create($previous));
        }
        return $e;
    }

    public function toArray()
    {
        $exceptions = [];
        foreach (array_merge([$this], $this->getAllPrevious()) as $exception) {
            $exceptions[] = [
                'message' => $exception->getMessage(),
                'class' => $exception->getClass(),
                'trace' => $exception->getTrace(),
            ];
        }

        return $exceptions;
    }

    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    public function setStatusCode($code)
    {
        $this->_statusCode = $code;
    }

    public function getClass()
    {
        return $this->_class;
    }

    public function setClass($class)
    {
        $this->_class = $class;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setFile($file)
    {
        $this->_file = $file;
    }

    public function getLine()
    {
        return $this->_line;
    }

    public function setLine($line)
    {
        $this->_line = $line;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function setMessage($message)
    {
        $this->_message = $message;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function getPrevious()
    {
        return $this->_previous;
    }

    public function setPrevious(FlattenException $previous)
    {
        $this->_previous = $previous;
    }

    public function getAllPrevious()
    {
        $exceptions = [];
        $e = $this;
        while ($e = $e->getPrevious()) {
            $exceptions[] = $e;
        }

        return $exceptions;
    }

    public function getTrace()
    {
        return $this->_trace;
    }

    public function setTraceFromException(\Exception $exception)
    {
        $this->setTrace($exception->getTrace(), $exception->getFile(), $exception->getLine());
    }

    public function setTrace($trace, $file, $line)
    {
        $this->_trace = [];
        $this->_trace[] = [
            'namespace' => '',
            'short_class' => '',
            'class' => '',
            'type' => '',
            'function' => '',
            'file' => $file,
            'line' => $line,
            'args' => [],
        ];
        foreach ($trace as $entry) {
            $class = '';
            $namespace = '';
            if (isset($entry['class'])) {
                $parts = explode('\\', $entry['class']);
                $class = array_pop($parts);
                $namespace = implode('\\', $parts);
            }

            $this->_trace[] = [
                'namespace' => $namespace,
                'short_class' => $class,
                'class' => isset($entry['class']) ? $entry['class'] : '',
                'type' => isset($entry['type']) ? $entry['type'] : '',
                'function' => isset($entry['function']) ? $entry['function'] : null,
                'file' => isset($entry['file']) ? $entry['file'] : null,
                'line' => isset($entry['line']) ? $entry['line'] : null,
                'args' => isset($entry['args']) ? $this->flattenArgs($entry['args']) : [],
            ];
        }
    }

    private function flattenArgs($args, $level = 0, &$count = 0)
    {
        $result = [];
        foreach ($args as $key => $value) {
            if (++$count > 1e4) {
                return ['array', '*SKIPPED over 10000 entries*'];
            }
            if ($value instanceof \__PHP_Incomplete_Class) {
                // is_object() returns false on PHP<=7.1
                $result[$key] = ['incomplete-object', $this->getClassNameFromIncomplete($value)];
            } elseif (is_object($value)) {
                $result[$key] = ['object', get_class($value)];
            } elseif (is_array($value)) {
                if ($level > 10) {
                    $result[$key] = ['array', '*DEEP NESTED ARRAY*'];
                } else {
                    $result[$key] = ['array', $this->flattenArgs($value, $level + 1, $count)];
                }
            } elseif (null === $value) {
                $result[$key] = ['null', null];
            } elseif (is_bool($value)) {
                $result[$key] = ['boolean', $value];
            } elseif (is_int($value)) {
                $result[$key] = ['integer', $value];
            } elseif (is_float($value)) {
                $result[$key] = ['float', $value];
            } elseif (is_resource($value)) {
                $result[$key] = ['resource', get_resource_type($value)];
            } else {
                $result[$key] = ['string', (string)$value];
            }
        }

        return $result;
    }

    private function getClassNameFromIncomplete(\__PHP_Incomplete_Class $value)
    {
        $array = new \ArrayObject($value);

        return $array['__PHP_Incomplete_Class_Name'];
    }
}