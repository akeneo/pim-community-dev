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
 * Report controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_importexport_report",
 *      name="Report manipulation",
 *      description="Report manipulation",
 *      parent="pim_importexport"
 * )
 */
class ReportController extends AbstractDoctrineController
{
    private $dataGridWorker;
    private $batchLogHandler;

    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $dataGridWorker,
        BatchLogHandler $batchLogHandler
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->dataGridWorker  = $dataGridWorker;
        $this->batchLogHandler = $batchLogHandler;
    }
    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $gridManager = $this->dataGridWorker->getDatagridManager('report', 'pim_import_export');

        return $this->renderDatagrid($gridManager);
    }

    /**
     * Download the log file of the job execution
     *
     * @Acl(
     *      id="pim_importexport_report_download",
     *      name="Download an import/export log",
     *      description="Download import/export log",
     *      parent="pim_importexport_report"
     * )
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
     * List the export reports
     *
     * @Acl(
     *      id="pim_importexport_report_import",
     *      name="View the list of export reports",
     *      description="View the list of export reports",
     *      parent="pim_importexport_report"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        $gridManager = $this->dataGridWorker->getDatagridManager('export_report', 'pim_import_export');

        return $this->renderDatagrid($gridManager);
    }

    /**
     * List the import reports
     *
     * @Acl(
     *      id="pim_importexport_report_export",
     *      name="View the list of import reports",
     *      description="View the list of import reports",
     *      parent="pim_importexport_report"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction()
    {
        $gridManager = $this->dataGridWorker->getDatagridManager('import_report', 'pim_import_export');

        return $this->renderDatagrid($gridManager);
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
            $view = 'PimImportExportBundle:Report:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagridView));
    }
}
