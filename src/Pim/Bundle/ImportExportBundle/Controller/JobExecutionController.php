<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Oro\Bundle\UserBundle\Annotation\Acl;

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
     * @var DatagridWorkerInterface
     */
    private $dataGridWorker;

    /**
     * @var BatchLogHandler
     */
    private $batchLogHandler;

    /**
     * @var string
     */
    private $jobType;

    /**
     * Constructor
     * @param Request $request
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface $validator
     * @param RegistryInterface $doctrine
     * @param DatagridWorkerInterface $dataGridWorker
     * @param BatchLogHandler $batchLogHandler
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $dataGridWorker,
        BatchLogHandler $batchLogHandler,
        $jobType
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->dataGridWorker  = $dataGridWorker;
        $this->batchLogHandler = $batchLogHandler;
        $this->jobType         = $jobType;
    }
    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $gridManager = $this->dataGridWorker->getDatagridManager($this->getJobType().'_report', 'pim_import_export');

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
        $jobExecution = $this->findOr404('PimBatchBundle:JobExecution', $id);
        $view = sprintf('PimImportExportBundle:%s:show.html.twig', ucfirst($this->getJobType()).'Report');

        return $this->render($view, array('execution' => $jobExecution));
    }

    /**
     * Download the log file of the job execution
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadLogFileAction($id)
    {
        $jobExecution = $this->findOr404('PimBatchBundle:JobExecution', $id);

        $response = new BinaryFileResponse($this->batchLogHandler->getRealPath($jobExecution->getLogFile()));
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
