<?php

namespace Oro\Bundle\CronBundle\Command\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Oro\Bundle\CronBundle\Command\Logger\Exception\RaiseExceptionLoggerException;

/**
 * This class uses $baseLogger to write log messages, but in additional it raises RaiseExceptionLoggerException
 * an exception alter a log message was wrote for the following levels: error, critical, alert and emergency
 */
class RaiseExceptionLogger extends AbstractLogger
{
    /**
     * @var LoggerInterface
     */
    protected $baseLogger;

    /**
     * Constructor
     *
     * @param LoggerInterface $baseLogger
     */
    public function __construct(LoggerInterface $baseLogger)
    {
        $this->baseLogger = $baseLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $this->baseLogger->log($level, $message, $context);

        switch ($level) {
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::EMERGENCY:
                // based on PSR-3 recommendations if an Exception object is passed in the context data,
                // it MUST be in the 'exception' key.
                if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
                    throw new RaiseExceptionLoggerException($message, 0, $context['exception']);
                }
                throw new RaiseExceptionLoggerException($message);
        }
    }
}
