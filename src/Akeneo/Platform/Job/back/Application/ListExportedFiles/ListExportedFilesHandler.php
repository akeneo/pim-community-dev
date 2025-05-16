<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\ListExportedFiles;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Platform\Job\ServiceApi\JobExecution\ListExportedFiles\ListExportedFilesHandlerInterface;
use Akeneo\Platform\Job\ServiceApi\JobExecution\ListExportedFiles\ListExportedFilesQuery;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;

class ListExportedFilesHandler implements ListExportedFilesHandlerInterface
{
    public function __construct(
        private readonly JobExecutionRepository $jobExecutionRepository,
        private FileWriterArchiver $fileWriterArchiver,
    ) {
    }

    public function handle(ListExportedFilesQuery $query): array
    {
        $jobExecutionId = $query->jobExecutionId;
        $withMedia = $query->withMedia;

        /** @var JobExecution $jobExecution */
        $jobExecution = $this->jobExecutionRepository->find($jobExecutionId);
        $jobInstance = $jobExecution->getJobInstance();

        if ($jobInstance->getType() !== JobInstance::TYPE_EXPORT) {
            throw new \Exception('Job Instance must be of type export.');
        }

        return $this->getGeneratedTemplateFilename($jobExecution, $withMedia);
    }

    private function getGeneratedTemplateFilename(JobExecution $jobExecution, bool $deep = false): array
    {
        $archives = $this->fileWriterArchiver->getArchives($jobExecution, $deep);

        $filePaths = [];
        foreach ($archives as $filePath) {
            $filePaths[] = $filePath;
        }

        return $filePaths;
    }
}
