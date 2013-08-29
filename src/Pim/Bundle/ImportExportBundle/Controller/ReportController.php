<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\ProductBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Report controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReportController extends Controller
{
    /**
     * List the reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $gridManager = $this->get('pim_import_export.datagrid.manager.report');

        return $this->renderDatagrid($gridManager);
    }

    public function downloadLogFileAction($id)
    {
        $jobExecution = $this->findOr404('PimBatchBundle:JobExecution', $id);

        $logger = $this->get('pim_batch.logger.batch_log_handler');

        $response = new BinaryFileResponse($logger->getRealPath($jobExecution->getLogFile()));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * List the export reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        $gridManager = $this->get('pim_import_export.datagrid.manager.export_report');

        return $this->renderDatagrid($gridManager);
    }

    /**
     * List the import reports
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction()
    {
        $gridManager = $this->get('pim_import_export.datagrid.manager.import_report');

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
