<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobWithStepsInterface;
use Akeneo\Tool\Component\Batch\Job\StoppableJobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\FilesystemReader;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

/**
 * Job execution tracker controller.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobTrackerController
{
    protected EventDispatcherInterface $eventDispatcher;
    protected JobExecutionRepository $jobExecutionRepo;
    protected JobExecutionArchivist $archivist;
    protected SecurityFacade $securityFacade;
    protected array $jobSecurityMapping;
    private SqlUpdateJobExecutionStatus $updateJobExecutionStatus;
    private JobRegistry $jobRegistry;
    private LoggerInterface $logger;
    private FilesystemReader $archivistFilesystem;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        JobExecutionRepository $jobExecutionRepo,
        JobExecutionArchivist $archivist,
        SecurityFacade $securityFacade,
        array $jobSecurityMapping,
        SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
        JobRegistry $jobRegistry,
        LoggerInterface $logger,
        FilesystemReader $archivistFilesystem
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->archivist = $archivist;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
        $this->updateJobExecutionStatus = $updateJobExecutionStatus;
        $this->jobRegistry = $jobRegistry;
        $this->logger = $logger;
        $this->archivistFilesystem = $archivistFilesystem;
    }

    /**
     * Download an archived file.
     *
     * @param int    $id
     * @param string $archiver
     * @param string $key
     */
    public function downloadFilesAction($id, $archiver, $key): StreamedFileResponse
    {
        $jobExecution = $this->jobExecutionRepo->find($id);

        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        if (!$this->isJobGranted($jobExecution)) {
            throw new AccessDeniedException();
        }

        $this->eventDispatcher->dispatch(new GenericEvent($jobExecution), JobExecutionEvents::PRE_DOWNLOAD_FILES);

        $stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedFileResponse($stream);
    }

    public function downloadZipArchiveAction(int $jobExecutionId): StreamedResponse
    {
        // Depending on the size of the generated archive, the download can be extremely long
        \set_time_limit(0);

        $jobExecution = $this->jobExecutionRepo->find($jobExecutionId);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }
        if (!$this->isJobGranted($jobExecution)) {
            throw new AccessDeniedException();
        }

        $zipArchiveName = $this->getZipArchiveName($jobExecution);

        $options = new Archive();
        $options->setContentType('application/octet-stream');
        // this is needed to prevent issues with truncated zip files
        $options->setZeroHeader(true);
        $options->setComment('Generated zip archive');
        $zip = new ZipStream($zipArchiveName, $options);

        return new StreamedResponse(
            function () use ($zip, $jobExecution) {
                foreach ($this->archivist->getArchives($jobExecution, true) as $archiver => $archiveNames) {
                    foreach ($archiveNames as $filePath => $archiveKey) {
                        $zip->addFileFromStream(
                            $filePath,
                            $this->archivistFilesystem->readStream($archiveKey)
                        );
                    }
                }

                $zip->finish();
            },
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => \sprintf('attachment; filename="%s"', $zipArchiveName),
            ]
        );
    }

    /**
     * Returns if a user has read permission on an import or export.
     *
     * @param JobExecution $jobExecution
     * @param mixed        $object
     */
    protected function isJobGranted($jobExecution, $object = null): bool
    {
        $jobExecutionType = $jobExecution->getJobInstance()->getType();
        if (!array_key_exists($jobExecutionType, $this->jobSecurityMapping)) {
            return true;
        }

        return $this->securityFacade->isGranted($this->jobSecurityMapping[$jobExecutionType], $object);
    }

    /**
     * Set the Job status to Stopping.
     */
    public function stopJobAction(int $id): JsonResponse
    {
        $jobExecution = $this->jobExecutionRepo->find($id);
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        $isStoppable = $jobExecution->isRunning() && $job instanceof StoppableJobInterface && $job->isStoppable();
        $isGranted = $this->securityFacade->isGranted('pim_importexport_stop_job');

        if ($isStoppable && $isGranted) {
            $this->logger->info('Stop job was requested', ['job_execution_id' => $id]);
            $this->updateJobExecutionStatus->updateByJobExecutionId($id, new BatchStatus(BatchStatus::STOPPING));
        }

        return new JsonResponse(['successful' => true]);
    }

    private function getZipArchiveName(JobExecution $jobExecution): string
    {
        $jobInstance = $jobExecution->getJobInstance();
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        if (!$job instanceof JobWithStepsInterface) {
            return 'archive.zip';
        }

        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep) {
                $writer = $step->getWriter();
                if ($writer instanceof ArchivableWriterInterface && $writer instanceof StepExecutionAwareInterface) {
                    foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                        if ($stepExecution->getStepName() === $step->getName()) {
                            $writer->setStepExecution($stepExecution);

                            return \sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME));
                        }
                    }
                }
            }
        }

        return 'archive.zip';
    }
}
