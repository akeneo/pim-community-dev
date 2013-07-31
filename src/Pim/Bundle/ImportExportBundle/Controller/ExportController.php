<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Export controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/export")
 */
class ExportController extends JobController
{
    /**
     * {@inheritdoc}
     *
     * @Route(
     *     "/.{_format}",
     *     name="pim_ie_export_index",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
     * )
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/create", name="pim_ie_export_create")
     * @Template("PimImportExportBundle:Export:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/{id}", name="pim_ie_export_show")
     * @Template("PimImportExportBundle:Export:show.html.twig")
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/edit/{id}", name="pim_ie_export_edit")
     * @Template("PimImportExportBundle:Export:edit.html.twig")
     */
    public function editAction($id)
    {
        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/{id}/remove", requirements={"id"="\d+"}, name="pim_ie_export_remove")
     * @Method("DELETE")
     */
    public function removeAction($id)
    {
        return parent::removeAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Route(
     *     "/report/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0},
     *     name="pim_ie_export_report"
     * )
     * @Template
     */
    public function reportAction($id)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @Route(
     *     "/launch/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0},
     *     name="pim_ie_export_launch"
     * )
     * @Template
     */
    public function launchAction($id)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getJobType()
    {
        return Job::TYPE_EXPORT;
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToShowView($jobId)
    {
        return $this->redirect(
            $this->generateUrl('pim_ie_export_show', array('id' => $jobId))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndexLogicName()
    {
        return 'PimImportExportBundle:Export:index.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getJobs()
    {
        return $this->getConnectorRegistry()->getExportJobs();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDatagridManager()
    {
        return $this->get('pim_import_export.datagrid.manager.export');
    }
}
