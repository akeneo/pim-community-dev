<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Pim\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Job execution controller
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController
{
    const BLOCK_SIZE = 8192;

    /** @var Request */
    protected $request;

    /** @var EngineInterface */
    protected $templating;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var BatchLogHandler */
    protected $batchLogHandler;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var string */
    protected $jobType;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var JobExecutionManager */
    protected $jobExecutionManager;

    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param BatchLogHandler          $batchLogHandler
     * @param JobExecutionArchivist    $archivist
     * @param SerializerInterface      $serializer
     * @param JobExecutionManager      $jobExecutionManager
     * @param JobExecutionRepository   $jobExecutionRepo
     * @param string                   $jobType
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        BatchLogHandler $batchLogHandler,
        JobExecutionArchivist $archivist,
        SerializerInterface $serializer,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        $jobType
    ) {
        $this->batchLogHandler = $batchLogHandler;
        $this->archivist = $archivist;
        $this->serializer = $serializer;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->templating = $templating;
        $this->request = $request;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->jobType = $jobType;
    }

    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            sprintf('PimEnrichBundle:MassEditExecution:index.html.twig', ucfirst($this->getJobType()))
        );
    }

    /**
     * Download the log file of the job execution
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadLogFileAction($id)
    {
        $jobExecution = $this->jobExecutionRepo->find($id);

        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Component\Batch\Model\JobExecution entity not found');
        }

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_LOG, new GenericEvent($jobExecution));

        $response = new BinaryFileResponse($jobExecution->getLogFile());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
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
            throw new NotFoundHttpException('Akeneo\Component\Batch\Model\JobExecution entity not found');
        }

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_FILES, new GenericEvent($jobExecution));

        $stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedFileResponse($stream);
    }

    /**
     * Return the job type of the controller
     *
     * @return string
     */
    protected function getJobType()
    {
        return $this->jobType;
    }
}
