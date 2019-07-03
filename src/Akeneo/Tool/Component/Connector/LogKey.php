<?php

namespace Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Model\JobExecution;

/**
 * Key of a log file stored in the "archivist" filesystem.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LogKey
{
    /** @var JobExecution */
    private $jobExecution;

    public function __construct(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;

        if (empty($jobExecution->getLogFile())) {
            throw new \InvalidArgumentException('The log file should not be empty');
        }

        if (!is_file($jobExecution->getLogFile())) {
            throw new \InvalidArgumentException('The log should exists.');
        }
    }

    public function __toString(): string
    {
        $jobInstance = $this->jobExecution->getJobInstance();

        return
            $jobInstance->getType() . DIRECTORY_SEPARATOR .
            $jobInstance->getJobName() . DIRECTORY_SEPARATOR .
            $this->jobExecution->getId() . DIRECTORY_SEPARATOR .
            'log' . DIRECTORY_SEPARATOR .
            basename($this->jobExecution->getLogFile())
        ;
    }
}
