<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Oro\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\BaseConnectorBundle\EventListener\JobExecutionArchivist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Gaufrette\StreamMode;

/**
 * Job execution controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController extends AbstractDoctrineController
{
    /**
     * @var BatchLogHandler
     */
    private $batchLogHandler;

    /**
     * @var JobExecutionArchivist
     */
    private $archivist;

    /**
     * @var string
     */
    private $jobType;

    /**
     * Constructor
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param BatchLogHandler          $batchLogHandler
     * @param JobExecutionArchivist    $archivist
     * @param string                   $jobType
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        BatchLogHandler $batchLogHandler,
        JobExecutionArchivist $archivist,
        $jobType
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->batchLogHandler = $batchLogHandler;
        $this->archivist       = $archivist;
        $this->jobType         = $jobType;
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
     * @param integer $id
     *
     * @return template
     */
    public function showAction($id)
    {
        $jobExecution = $this->findOr404('OroBatchBundle:JobExecution', $id);

        return $this->render(
            sprintf('PimImportExportBundle:%sExecution:show.html.twig', ucfirst($this->getJobType())),
            array(
                'execution'   => $jobExecution,
                'existingLog' => file_exists($jobExecution->getLogFile()),
                'archives'    => $this->archivist->getArchives($jobExecution),
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
        $jobExecution = $this->findOr404('OroBatchBundle:JobExecution', $id);

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
        $jobExecution = $this->findOr404('OroBatchBundle:JobExecution', $id);
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
