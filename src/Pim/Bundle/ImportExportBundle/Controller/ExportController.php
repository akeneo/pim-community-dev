<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\BatchBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Connector;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Entity\RawConfiguration;

/**
 * Export controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/export")
 */
class ExportController extends Controller
{
    /**
     * List exports
     * @param Request $request
     *
     * @Route("/.{_format}",
     *      name="pim_ie_export_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ExportDatagridManager */
        $gridManager = $this->get('pim_import_export.datagrid.manager.export');
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimImportExportBundle:Export:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagridView));
    }
}
