<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller;

use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\StepExecutionArchivist;
use Akeneo\Tool\Component\Connector\LogKey;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Job execution controller.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController
{
    protected EventDispatcherInterface $eventDispatcher;
    protected StepExecutionArchivist $archivist;
    protected JobExecutionRepository $jobExecutionRepo;
    private FilesystemReader $logFileSystem;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StepExecutionArchivist $archivist,
        JobExecutionRepository $jobExecutionRepo,
        FilesystemReader $logFileSystem
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->archivist = $archivist;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->logFileSystem = $logFileSystem;
    }

    /**
     * Download the log file of the job execution.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws UnableToReadFile
     */
    public function downloadLogFileAction($id)
    {
        $jobExecution = $this->jobExecutionRepo->find($id);

        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        $this->eventDispatcher->dispatch(new GenericEvent($jobExecution), JobExecutionEvents::PRE_DOWNLOAD_LOG);

        $logFileContent = $this->logFileSystem->read(new LogKey($jobExecution));

        $response = new Response($logFileContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($jobExecution->getLogFile())
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Download an archived file.
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

        $this->eventDispatcher->dispatch(new GenericEvent($jobExecution), JobExecutionEvents::PRE_DOWNLOAD_FILES);

        $stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedFileResponse($stream);
    }
}
