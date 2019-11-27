<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\Ui;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array */
    protected $jobSecurityMapping;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobExecutionRepository   $jobExecutionRepo
     * @param JobExecutionArchivist    $archivist
     * @param SecurityFacade           $securityFacade
     * @param array                    $jobSecurityMapping
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        JobExecutionRepository $jobExecutionRepo,
        JobExecutionArchivist $archivist,
        SecurityFacade $securityFacade,
        $jobSecurityMapping
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->archivist = $archivist;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
    }

    /**
     * Download an archived file
     *
     * @param int    $id
     * @param string $archiver
     * @param string $key
     *
     * @return StreamedResponse
     */
    public function downloadFilesAction($id, $archiver, $key)
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
     *
     * @return bool
     */
    protected function isJobGranted($jobExecution, $object = null)
    {
        $jobExecutionType = $jobExecution->getJobInstance()->getType();
        if (!array_key_exists($jobExecutionType, $this->jobSecurityMapping)) {
            return true;
        }

        return $this->securityFacade->isGranted($this->jobSecurityMapping[$jobExecutionType], $object);
    }
}
