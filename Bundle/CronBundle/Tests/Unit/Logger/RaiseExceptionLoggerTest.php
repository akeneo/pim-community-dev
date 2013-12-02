<?php

namespace Oro\Bundle\CronBundle\Tests\Unit\Command\Logger;

use Psr\Log\LogLevel;
use Oro\Bundle\CronBundle\Command\Logger\RaiseExceptionLogger;
use Oro\Bundle\CronBundle\Command\Logger\Exception\RaiseExceptionLoggerException;

class RaiseExceptionLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $baseLogger;

    /** @var RaiseExceptionLogger */
    protected $logger;

    protected function setUp()
    {
        $this->baseLogger = $this->getMock('Psr\Log\LoggerInterface');
        $this->logger = new RaiseExceptionLogger($this->baseLogger);
    }

    /**
     * @dataProvider withoutExceptionProvider
     */
    public function testLogWithoutException($level)
    {
        $msg = 'test';
        $context = array('test' => 'test');
        $this->baseLogger->expects($this->once())
            ->method('log')
            ->with($level, $msg, $context);
        $this->logger->log($level, $msg, $context);
    }

    /**
     * @dataProvider withExceptionProvider
     */
    public function testLogWithException($level, $exception)
    {
        $msg = 'test';
        $context = array('test' => 'test');
        if ($exception !== null) {
            $context['exception'] = $exception;
        }
        $this->baseLogger->expects($this->once())
            ->method('log')
            ->with($level, $msg, $context);
        try {
            $this->logger->log($level, $msg, $context);
            $this->fail('Expected "Oro\Bundle\CronBundle\Command\Logger\Exception\RaiseExceptionLoggerException"');
        } catch (RaiseExceptionLoggerException $ex) {
            $this->assertEquals($exception, $ex->getPrevious());
        }
    }

    public function withoutExceptionProvider()
    {
        return array(
            array(LogLevel::WARNING),
            array(LogLevel::NOTICE),
            array(LogLevel::INFO),
            array(LogLevel::DEBUG),
        );
    }

    public function withExceptionProvider()
    {
        return array(
            array(LogLevel::EMERGENCY, null),
            array(LogLevel::EMERGENCY, new \LogicException()),
            array(LogLevel::ALERT, null),
            array(LogLevel::ALERT, new \LogicException()),
            array(LogLevel::CRITICAL, null),
            array(LogLevel::CRITICAL, new \LogicException()),
            array(LogLevel::ERROR, null),
            array(LogLevel::ERROR, new \LogicException()),
        );
    }
}
