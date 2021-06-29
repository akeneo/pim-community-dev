<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Storage;

use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\GetJobExecutionIds;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteJobExecutionLogs
{
    /** @var GetJobExecutionIds */
    private $getJobExecutionIds;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $logDir;

    public function __construct(
        GetJobExecutionIds $getJobExecutionIds,
        Filesystem $filesystem,
        string $logDir
    ) {
        $this->getJobExecutionIds = $getJobExecutionIds;
        $this->filesystem = $filesystem;
        $this->logDir = $logDir;
    }

    public function olderThanDays(int $days): void
    {
        $statement = $this->getJobExecutionIds->olderThanDays($days);
        while ($id = $statement->fetch(FetchMode::COLUMN)) {
            $this->filesystem->remove($this->getJobExecutionLogDirectory($id));
        }
    }

    public function all(): void
    {
        $statement = $this->getJobExecutionIds->all();
        while ($id = $statement->fetch(FetchMode::COLUMN)) {
            $this->filesystem->remove($this->getJobExecutionLogDirectory($id));
        }
    }

    private function getJobExecutionLogDirectory(string $jobExecutionId): string
    {
        return sprintf('%s/%s', $this->logDir, $jobExecutionId);
    }
}
