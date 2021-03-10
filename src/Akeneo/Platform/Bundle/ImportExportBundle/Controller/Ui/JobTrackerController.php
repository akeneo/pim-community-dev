<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\StoppableJobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Query\SqlUpdateJobExecutionStatus;
use Akeneo\Tool\Component\FileStorage\File\FileFetcher;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

/**
 * Job execution tracker controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobTrackerController extends Controller
{
    protected EventDispatcherInterface $eventDispatcher;
    protected JobExecutionRepository $jobExecutionRepo;
    protected JobExecutionArchivist $archivist;
    protected SecurityFacade $securityFacade;
    protected array $jobSecurityMapping;
    private SqlUpdateJobExecutionStatus $updateJobExecutionStatus;
    private JobRegistry $jobRegistry;
    private LoggerInterface $logger;

    private FilesystemProvider $fsProvider;
    private Connection $connection;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        JobExecutionRepository $jobExecutionRepo,
        JobExecutionArchivist $archivist,
        SecurityFacade $securityFacade,
        array $jobSecurityMapping,
        SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
        JobRegistry $jobRegistry,
        LoggerInterface $logger,
        FilesystemProvider $fsProvider,
        Connection $connection
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->archivist = $archivist;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
        $this->updateJobExecutionStatus = $updateJobExecutionStatus;
        $this->jobRegistry = $jobRegistry;
        $this->logger = $logger;
        $this->fsProvider = $fsProvider;
        $this->fileFetcher = $fileFetcher;
        $this->connection = $connection;
    }

    /**
     * Download an archived file
     *
     * @param int    $id
     * @param string $archiver
     * @param string $key
     */
    public function downloadFilesAction($id, $archiver, $key): StreamedResponse
    {
        $jobExecution = $this->jobExecutionRepo->find($id);

        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        if (!$this->isJobGranted($jobExecution)) {
            throw new AccessDeniedException();
        }

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_FILES, new GenericEvent($jobExecution));

        //$stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        $files = $this->getRandomFiles();
        $fsProvider = $this->fsProvider;

        $stream = function () use ($files, $fsProvider, $key) {
            $options = new Archive();
            $options->setContentType('application/octet-stream');
            // this is needed to prevent issues with truncated zip files
            $options->setZeroHeader(true);
            $options->setComment('test zip file.');

            $zip = new ZipStream($key, $options);

            $csvResource = \tmpfile();
            \fwrite($csvResource, \sprintf('%s;%s%s', 'sku', 'image', PHP_EOL));

            foreach ($files as $filePath => $props) {
                $filesystem = $fsProvider->getFilesystem($props['storage']);
                $streamedFile = $filesystem->readStream($props['key']);
                $zip->addFileFromStream($filePath, $streamedFile);

                \fwrite($csvResource, \sprintf('%s;"%s"%s', $props['sku'], $filePath, PHP_EOL));
            }
            \rewind($csvResource);
            $zip->addFileFromStream('export.csv', $csvResource);
            \fclose($csvResource);

            $zip->finish();
        };

        return new StreamedResponse($stream, 200, ['Content-Type' => 'application/octet-stream']);
    }

    /**
     * Returns if a user has read permission on an import or export
     *
     * @param JobExecution  $jobExecution
     * @param mixed $object The object
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
     * Set the Job status to Stopping
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

    private function getRandomFiles(): array
    {
        $sql = <<<SQL
SELECT file_key, original_filename, storage
FROM akeneo_file_storage_file_info
LIMIT 1000
SQL;

        $files = [];
        $rows = $this->connection->executeQuery($sql)->fetchAll();
        $numberOfRows = count($rows);

        for ($i = 0; $i < 1000; $i++) {
            $row = $rows[$i % $numberOfRows];
            $sku = \uniqid();
            $filepath = \sprintf('files/%s/%s', $sku, $row['original_filename']);
            $files[$filepath] = [
                'sku' => $sku,
                'key' => $row['file_key'],
                'storage' => $row['storage'],
            ];
        }

        return $files;
    }
}
