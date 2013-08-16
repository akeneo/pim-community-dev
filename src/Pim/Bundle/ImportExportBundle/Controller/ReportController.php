<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\ProductBundle\Controller\Controller;

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
