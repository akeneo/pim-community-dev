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
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        JobExecutionRepository $jobExecutionRepo,
        JobExecutionArchivist $archivist,
        SecurityFacade $securityFacade,
        array $jobSecurityMapping,
        SqlUpdateJobExecutionStatus $updateJobExecutionStatus,
        JobRegistry $jobRegistry,
        LoggerInterface $logger
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->archivist = $archivist;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
        $this->updateJobExecutionStatus = $updateJobExecutionStatus;
        $this->jobRegistry = $jobRegistry;
        $this->logger = $logger;
    }

    /**
     * Download an archived file
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

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_FILES, new GenericEvent($jobExecution));

        $stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedFileResponse($stream);
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
}
