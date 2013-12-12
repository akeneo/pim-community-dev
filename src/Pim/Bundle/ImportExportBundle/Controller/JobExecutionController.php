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

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
use Pim\Bundle\ImportExportBundle\Archiver\JobExecutionArchiver;

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
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

    /**
     * @var BatchLogHandler
     */
    private $batchLogHandler;

    /**
     * @var JobExecutionArchiver
     */
    private $jobExecutionArchiver;

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
     * @param DatagridHelperInterface  $datagridHelper
     * @param BatchLogHandler          $batchLogHandler
     * @param JobExecutionArchiver     $archiver
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
        DatagridHelperInterface $datagridHelper,
        BatchLogHandler $batchLogHandler,
        JobExecutionArchiver $archiver,
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

        $this->datagridHelper       = $datagridHelper;
        $this->batchLogHandler      = $batchLogHandler;
        $this->jobExecutionArchiver = $archiver;
        $this->jobType              = $jobType;
    }
    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $gridManager = $this->datagridHelper->getDatagridManager($this->getJobType().'_execution', 'pim_import_export');

        return $this->renderDatagrid($gridManager);
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
        $view = sprintf('PimImportExportBundle:%s:show.html.twig', ucfirst($this->getJobType()).'Report');

        return $this->render(
            $view,
            array(
                'execution'        => $jobExecution,
                'existingLog'      => file_exists($this->batchLogHandler->getRealPath($jobExecution->getLogFile())),
                'existingDownload' => file_exists($this->jobExecutionArchiver->getDownloadPath($jobExecution)),
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

        $response = new BinaryFileResponse($this->batchLogHandler->getRealPath($jobExecution->getLogFile()));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * Download the input / output files of the job execution
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadFilesAction($id)
    {
        $jobExecution = $this->findOr404('OroBatchBundle:JobExecution', $id);
        $path = $this->jobExecutionArchiver->getDownloadPath($jobExecution);
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * Render the report datagrid from a datagrid manager
     *
     * @param \Pim\Bundle\ImportExportBundle\Datagrid\ReportDatagridManager $gridManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderDatagrid($gridManager)
    {
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = sprintf('PimImportExportBundle:%s:index.html.twig', ucfirst($this->getJobType()).'Report');
        }

        return $this->render($view, array('datagrid' => $datagridView));
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
