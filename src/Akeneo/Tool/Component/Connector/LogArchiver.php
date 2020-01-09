<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use League\Flysystem\Filesystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Archives logs of the import/export in the "archivist" filesystem.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LogArchiver implements EventSubscriberInterface
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function archive(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $logPath = $jobExecution->getLogFile();

        if (is_file($logPath)) {
            $log = fopen($logPath, 'r');
            $this->filesystem->writeStream(new LogKey($jobExecution), $log);
            if (is_resource($log)) {
                fclose($log);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'archive'
        ];
    }
}
