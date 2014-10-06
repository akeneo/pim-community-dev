<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Doctrine\Common\Persistence\ManagerRegistry;
use Gaufrette\StreamMode;
use Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Job execution controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController extends AbstractDoctrineController
{
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

    /**
     * Constructor
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param BatchLogHandler          $batchLogHandler
     * @param JobExecutionArchivist    $archivist
     * @param string                   $jobType
     * @param SerializerInterface      $serializer
     * @param JobExecutionManager      $jobExecutionManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        BatchLogHandler $batchLogHandler,
        JobExecutionArchivist $archivist,
        $jobType,
        SerializerInterface $serializer,
        JobExecutionManager $jobExecutionManager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->batchLogHandler     = $batchLogHandler;
        $this->archivist           = $archivist;
        $this->jobType             = $jobType;
        $this->serializer          = $serializer;
        $this->jobExecutionManager = $jobExecutionManager;
    }

    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render(
            sprintf('PimImportExportBundle:%sExecution:index.html.twig', ucfirst($this->getJobType()))
        );
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

            return new JsonResponse(
                [
                    'jobExecution' => $this->serializer->normalize($jobExecution, 'json'),
                    'hasLog'       => file_exists($jobExecution->getLogFile()),
                    'archives'     => $archives,
                ]
            );
        }

        return $this->render(
            sprintf('PimImportExportBundle:%sExecution:show.html.twig', ucfirst($this->getJobType())),
            array(
                'execution' => $jobExecution,
            )
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

        $stream       = $this->archivist->getArchive($jobExecution, $archiver, $key);

        return new StreamedResponse(
            function () use ($stream) {
                $stream->open(new StreamMode('rb'));
                while (!$stream->eof()) {
                    echo $stream->read(8192);
                }
                $stream->close();
            },
            200,
            array('Content-Type' => 'application/octet-stream')
        );
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
