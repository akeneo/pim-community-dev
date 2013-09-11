<?php

namespace Oro\Bundle\CronBundle\Command\Logger;

/**
 * Describes a logger instance
 */
interface LoggerInterface
{
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     */
    public function error($message);

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     */
    public function warning($message);

    /**
     * Normal but significant events.
     *
     * @param string $message
     */
    public function notice($message);

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     */
    public function info($message);

    /**
     * Detailed debug information.
     *
     * @param string $message
     */
    public function debug($message);
}
