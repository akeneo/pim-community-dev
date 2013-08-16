<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/import")
 */
class ImportController extends JobControllerAbstract
{
    /**
     * {@inheritdoc}
     *
     * @Route(
     *     "/.{_format}",
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
     * @Route("/create")
     * @Template("PimImportExportBundle:Import:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/{id}")
     * @Template
     */
    public function showAction($id)
    {
        return parent::showAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/edit/{id}")
     * @Template
     */
    public function editAction($id)
    {
        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     *
     * @Route("/{id}/remove", requirements={"id"="\d+"})
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
     *     "/show/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
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
     *     defaults={"id"=0}
     * )
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
        return Job::TYPE_IMPORT;
    }

    /**
     * {@inheritdoc}
     */
    protected function redirectToShowView($jobId)
    {
        return $this->redirect(
            $this->generateUrl('pim_importexport_import_show', array('id' => $jobId))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndexRouteName()
    {
        return 'pim_importexport_import_index';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndexLogicName()
    {
        return 'PimImportExportBundle:Import:index.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDatagridManager()
    {
        return $this->get('pim_import_export.datagrid.manager.import');
    }
}
