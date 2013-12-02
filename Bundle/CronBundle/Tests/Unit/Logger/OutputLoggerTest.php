<?php

namespace Oro\Bundle\CronBundle\Tests\Unit\Command\Logger;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;

class OutputLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $output;

    /** @var OutputLogger */
    protected $logger;

    protected function setUp()
    {
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->logger = new OutputLogger($this->output);
    }

    /**
     * @dataProvider itemProvider
     */
    public function testLog($expectWriteToOutput, $verbosity, $level, $message, $context)
    {
        $this->output->expects($this->any())
            ->method('getVerbosity')
            ->will($this->returnValue($verbosity));

        if ($expectWriteToOutput) {
            if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
                $this->output->expects($this->exactly(2))
                    ->method('writeln');
            } else {
                $this->output->expects($this->exactly(1))
                    ->method('writeln');
            }
        } else {
            $this->output->expects($this->never())
                ->method('writeln');
        }

        $this->logger->log($level, $message, $context);
    }

    public function itemProvider()
    {
        return array(
            array(
                true,
                OutputInterface::VERBOSITY_QUIET,
                LogLevel::EMERGENCY,
                'test',
                array('exception' => new \Exception())
            ),
            array(true, OutputInterface::VERBOSITY_QUIET, LogLevel::EMERGENCY, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::EMERGENCY, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::EMERGENCY, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::EMERGENCY, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::EMERGENCY, 'test', array()),

            array(
                true,
                OutputInterface::VERBOSITY_QUIET,
                LogLevel::ALERT,
                'test',
                array('exception' => new \Exception())
            ),
            array(true, OutputInterface::VERBOSITY_QUIET, LogLevel::ALERT, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::ALERT, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::ALERT, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::ALERT, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::ALERT, 'test', array()),

            array(
                true,
                OutputInterface::VERBOSITY_QUIET,
                LogLevel::CRITICAL,
                'test',
                array('exception' => new \Exception())
            ),
            array(true, OutputInterface::VERBOSITY_QUIET, LogLevel::CRITICAL, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::CRITICAL, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::CRITICAL, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::CRITICAL, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::CRITICAL, 'test', array()),

            array(
                true,
                OutputInterface::VERBOSITY_QUIET,
                LogLevel::ERROR,
                'test',
                array('exception' => new \Exception())
            ),
            array(true, OutputInterface::VERBOSITY_QUIET, LogLevel::ERROR, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::ERROR, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::ERROR, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::ERROR, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::ERROR, 'test', array()),

            array(false, OutputInterface::VERBOSITY_QUIET, LogLevel::WARNING, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::WARNING, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::WARNING, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::WARNING, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::WARNING, 'test', array()),

            array(false, OutputInterface::VERBOSITY_QUIET, LogLevel::NOTICE, 'test', array()),
            array(true, OutputInterface::VERBOSITY_NORMAL, LogLevel::NOTICE, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::NOTICE, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::NOTICE, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::NOTICE, 'test', array()),

            array(false, OutputInterface::VERBOSITY_QUIET, LogLevel::INFO, 'test', array()),
            array(false, OutputInterface::VERBOSITY_NORMAL, LogLevel::INFO, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERBOSE, LogLevel::INFO, 'test', array()),
            array(true, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::INFO, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::INFO, 'test', array()),

            array(false, OutputInterface::VERBOSITY_QUIET, LogLevel::DEBUG, 'test', array()),
            array(false, OutputInterface::VERBOSITY_NORMAL, LogLevel::DEBUG, 'test', array()),
            array(false, OutputInterface::VERBOSITY_VERBOSE, LogLevel::DEBUG, 'test', array()),
            array(false, OutputInterface::VERBOSITY_VERY_VERBOSE, LogLevel::DEBUG, 'test', array()),
            array(true, OutputInterface::VERBOSITY_DEBUG, LogLevel::DEBUG, 'test', array()),
        );
    }
}
