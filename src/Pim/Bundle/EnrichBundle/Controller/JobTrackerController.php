<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Pim\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Job execution tracker controller
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobTrackerController extends Controller
{
    const BLOCK_SIZE = 8192;

    /** @var EngineInterface */
    protected $templating;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var EventSubscriberInterface */
    protected $jobExecutionManager;

    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /**
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobExecutionRepository   $jobExecutionRepo
     * @param JobExecutionArchivist    $archivist
     * @param SerializerInterface      $serializer
     * @param JobExecutionManager      $jobExecutionManager
     */
    public function __construct(
        EngineInterface $templating,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        JobExecutionRepository $jobExecutionRepo,
        JobExecutionArchivist $archivist,
        SerializerInterface $serializer,
        JobExecutionManager $jobExecutionManager
    ) {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->archivist = $archivist;
        $this->serializer = $serializer;
        $this->jobExecutionManager = $jobExecutionManager;
    }

    /**
     * List jobs execution
     *
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Show a job executions report
     *
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\Response|JsonResponse
     */
    public function showAction($id)
    {
        return $this->render(
            'PimEnrichBundle:JobExecution:show.html.twig',
            ['id' => $id]
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
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = [], Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }
}
