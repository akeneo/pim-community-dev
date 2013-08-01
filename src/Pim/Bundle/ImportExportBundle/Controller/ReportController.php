<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pim\Bundle\ProductBundle\Controller\Controller;

/**
 * Report controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * List the reports
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route(
     *     "/.{_format}",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format"="html"}
     * )
     */
    public function indexAction(Request $request)
    {
        $gridManager = $this->get('pim_import_export.datagrid.manager.report');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimImportExportBundle:Report:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagridView));
    }

    // TODO : the same for export action
    // TODO : the same for import action
}
