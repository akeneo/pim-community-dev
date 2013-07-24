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
     * @Route(
     *     "/.{_format}",
     *     name="pim_ie_export_index",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
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

    /**
     * Create export
     *
     * @Route(
     *     "/create",
     *     name="pim_ie_export_create"
     * )
     * @Template("PimImportExportBundle:Export:create.html.twig")
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        // get parameters
        $connector = $request->query->get('connector');
        $jobType   = $request->query->get('job_type');
        $jobAlias  = $request->query->get('job_alias');

        $registry = $this->get('pim_batch.connectors');
        $jobDefinition = $registry->getJob($connector, $jobType, $jobAlias);

        $job = new Job();
        $job->setJobDefinition($jobDefinition);


        return array(
            'form' => $this->createForm(new JobType(), $job)
        );
    }

    /**
     * Edit job
     *
     * @param Job $job
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Job $job)
    {
        if ($this->get('pim_config.form.handler.locale')->process($locale)) {
            $this->get('session')->getFlashBag()->add('success', 'Locale successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_config_locale_index')
            );
        }

        return array(
            'form' => $this->get('pim_config.form.locale')->createView()
        );
    }
}
