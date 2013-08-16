<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Export controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportController extends JobControllerAbstract
{
    /**
     * {@inheritdoc}
     *
     * @Template("PimImportExportBundle:Export:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     */
    public function editAction($id)
    {
        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     */
    public function reportAction($id)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @Template
     */
    public function launchAction($id)
    {
        return parent::launchAction($id);
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
            $this->generateUrl('pim_importexport_export_show', array('id' => $jobId))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndexRouteName()
    {
        return 'pim_importexport_export_index';
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
    protected function getDatagridManager()
    {
        return $this->get('pim_import_export.datagrid.manager.export');
    }
}
