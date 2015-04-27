<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Gaufrette\StreamMode;
use Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Templating\EngineInterface;
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
    /** @var EngineInterface */
    protected $templating;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var JobExecutionManager */
    protected $jobExecutionManager;

    /**
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param JobExecutionArchivist    $archivist
     * @param SerializerInterface      $serializer
     * @param JobExecutionManager      $jobExecutionManager
     */
    public function __construct(
        EngineInterface $templating,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        JobExecutionArchivist $archivist,
        SerializerInterface $serializer,
        JobExecutionManager $jobExecutionManager
    ) {
        $this->templating          = $templating;
        $this->translator          = $translator;
        $this->eventDispatcher     = $eventDispatcher;
        $this->doctrine            = $doctrine;
        $this->archivist           = $archivist;
        $this->serializer          = $serializer;
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
     * Show a report
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response|JsonResponse
     */
    public function showAction(Request $request, $id)
    {
        $jobExecution = $this->findOr404('AkeneoBatchBundle:JobExecution', $id);
        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_SHOW, new GenericEvent($jobExecution));

        if ('json' === $request->getRequestFormat()) {
            $archives = [];
            foreach ($this->archivist->getArchives($jobExecution) as $key => $files) {
                $label = $this->translator->transchoice(
                    sprintf('pim_import_export.download_archive.%s', $key),
                    count($files)
                );
                $archives[$key] = [
                    'label' => ucfirst($label),
                    'files' => $files,
                ];
            }

            if (!$this->jobExecutionManager->checkRunningStatus($jobExecution)) {
                $this->jobExecutionManager->markAsFailed($jobExecution);
            }

            // limit the number of step execution returned to avoid memory overflow
            $context = ['limit_warnings' => 100];

            return new JsonResponse(
                [
                    'jobExecution' => $this->serializer->normalize($jobExecution, 'json', $context),
                    'hasLog'       => file_exists($jobExecution->getLogFile()),
                    'archives'     => $archives,
                ]
            );
        }

        return $this->render(
            'PimEnrichBundle:JobTracker:show.html.twig',
            ['execution' => $jobExecution]
        );
    }

    /**
     * Download the log file of the job execution
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadLogFileAction($id)
    {
        $jobExecution = $this->findOr404('AkeneoBatchBundle:JobExecution', $id);

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_LOG, new GenericEvent($jobExecution));

        $response = new BinaryFileResponse($jobExecution->getLogFile());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * Download an archived file
     *
     * @param integer $id
     * @param string  $archiver
     * @param string  $key
     *
     * @return StreamedResponse
     */
    public function downloadFilesAction($id, $archiver, $key)
    {
        $jobExecution = $this->findOr404('AkeneoBatchBundle:JobExecution', $id);

        $this->eventDispatcher->dispatch(JobExecutionEvents::PRE_DOWNLOAD_FILES, new GenericEvent($jobExecution));

        $stream = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedResponse(
            function () use ($stream) {
                $stream->open(new StreamMode('rb'));
                while (!$stream->eof()) {
                    echo $stream->read(8192);
                }
                $stream->close();
            },
            200,
            ['Content-Type' => 'application/octet-stream']
        );
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

    /**
     * Find an entity or throw a 404
     *
     * @param string  $className Example: 'PimCatalogBundle:Category'
     * @param integer $id        The id of the entity
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return object
     */
    protected function findOr404($className, $id)
    {
        $result = $this->doctrine->getRepository($className)->find($id);

        if (!$result) {
            throw $this->createNotFoundException(sprintf('%s entity not found', $className));
        }

        return $result;
    }
}
