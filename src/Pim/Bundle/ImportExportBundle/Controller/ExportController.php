<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ImportExportBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\ProductBundle\Controller\Controller;

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
        /** @var $gridManager JobDatagridManager */
        $gridManager = $this->get('pim_import_export.datagrid.manager.export');
        $datagridView = $gridManager->getDatagrid()->createView();
        $registry      = $this->getConnectorRegistry();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimImportExportBundle:Export:index.html.twig';
        }

        return $this->render(
            $view,
            array(
                'datagrid' => $datagridView,
                'connectors' => $registry->getExportJobs(),
            )
        );
    }

    /**
     * Create export
     * @param Request $request
     *
     * @Route(
     *     "/create",
     *     name="pim_ie_export_create"
     * )
     * @Template("PimImportExportBundle:Export:edit.html.twig")
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $connector     = $request->query->get('connector');
        $alias         = $request->query->get('alias');
        $registry      = $this->getConnectorRegistry();

        $job = new Job($connector, Job::TYPE_EXPORT, $alias);

        if (!$jobDefinition = $registry->getJob($job)) {
            $this->addFlash('error', 'Fail to create an export with an unknown job.');

            return $this->redirectToRoute('pim_ie_export_index');
        }
        $job->setJobDefinition($jobDefinition);

        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash('success', 'The export has been successfully created.');

                return $this->redirectToRoute('pim_ie_export_show', array('id' => $job->getId()));
            }
        }

        return array(
            'form'      => $form->createView(),
            'connector' => $connector,
            'alias'     => $alias,
        );
    }

    /**
     * Show export
     * @param integer $id
     *
     * @Route(
     *     "/{id}",
     *     name="pim_ie_export_show"
     * )
     * @Template("PimImportExportBundle:Export:show.html.twig")
     *
     * @return array
     */
    public function showAction($id)
    {
        $job = $this->getJob($id);

        return array(
            'job'        => $job,
            'violations' => $this->getValidator()->validate($job),
        );
    }

    /**
     * Edit an export
     * @param integer $id
     *
     * @Route(
     *     "/edit/{id}",
     *     name="pim_ie_export_edit"
     * )
     * @Template("PimImportExportBundle:Export:edit.html.twig")
     *
     * @return array
     */
    public function editAction($id)
    {
        $job  = $this->getJob($id);
        $form = $this->createForm(new JobType(), $job);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash('success', 'The export has been successfully updated.');

                return $this->redirect($this->generateUrl('pim_ie_export_show', array('id' => $job->getId())));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * Delete a job
     *
     * @param Job $job
     *
     * @Route("/{id}/remove", requirements={"id"="\d+"}, name="pim_ie_export_remove")
     * @Method("DELETE")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Job $job)
    {
        $this->remove($job);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Job successfully removed');

            return $this->redirectToRoute('pim_ie_export_index');
        }
    }

    /**
     * View report for a job
     *
     * @param Job $job
     *
     * @Route(
     *     "/show/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0},
     *     name="pim_ie_import_report"
     * )
     * @Template
     *
     * @return array
     */
    public function reportAction(Job $job)
    {
    }

    /**
     * Get a job
     *
     * @param integer $id
     *
     * @return Job|RedirectResponse
     *
     * @throw NotFoundHttpException
     */
    protected function getJob($id)
    {
        $job           = $this->findOr404('PimBatchBundle:Job', $id);
        $registry      = $this->getConnectorRegistry();
        $jobDefinition = $registry->getJob($job);
        if (!$jobDefinition) {
            $this->addFlash(
                'error',
                sprintf(
                    'The following job does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $job->getConnector(),
                    $job->getType(),
                    $job->getAlias()
                )
            );

            return $this->redirectToRoute('pim_ie_export_index');
        }
        $job->setJobDefinition($jobDefinition);

        return $job;
    }

    /**
     * @return \Pim\Bundle\BatchBundle\Connector\ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->get('pim_batch.connectors');
    }
}
