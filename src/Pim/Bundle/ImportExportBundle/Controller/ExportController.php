<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
        $registry      = $this->get('pim_batch.connectors');

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimImportExportBundle:Export:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagridView, 'connectors' => $registry->getExportJobs()));
    }

    /**
     * Create export
     * @param Request $request
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
        $connector     = $request->query->get('connector');
        $alias         = $request->query->get('alias');
        $registry      = $this->get('pim_batch.connectors');
        $jobDefinition = $registry->getJob($connector, Job::TYPE_EXPORT, $alias);

        if (!$jobDefinition) {
            $this->addFlash('error', 'Fail to create an export with an unknown job.');

            return $this->redirect($this->generateUrl('pim_ie_export_index'));
        }

        $job = new Job($connector, Job::TYPE_EXPORT, $alias, $jobDefinition);

        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($job);
                $em->flush();

                $this->addFlash('success', 'The export has been successfully created.');

                return $this->redirect(
                    $this->generateUrl('pim_ie_export_index')
                );
            }
        }

        return array(
            'form'      => $form->createView(),
            'connector' => $connector,
            'alias'     => $alias,
        );
    }

    /**
     * Edit job
     *
     * @param Job $job
     *
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0},
     *     name="pim_ie_export_edit"
     * )
     * @Template
     *
     * @return array
     */
    public function editAction(Job $job)
    {
        $request = $this->getRequest();
        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($job);
                $em->flush();

                $this->addFlash('success', 'The export has been successfully updated.');

                return $this->redirect(
                    $this->generateUrl('pim_ie_export_index')
                );
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Delete a job
     *
     * @param Job $job
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"}, name="pim_ie_export_remove")
     * @Method("DELETE")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Job $job)
    {
        $this->getManager()->remove($job);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlashMessage('success', 'Job successfully removed');

            return $this->redirectIndex();
        }
    }

    /**
     * Redirect to index
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectIndex()
    {
        return $this->redirect($this->generateUrl('pim_ie_export_index'));
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
     *     name="pim_ie_export_report"
     * )
     * @Template
     *
     * @return array
     */
    public function reportAction(Job $job)
    {
    }

    /**
     * Launch a job
     *
     * @param Job $job
     *
     * @Route("/launch/{id}", requirements={"id"="\d+"}, defaults={"id"=0}, name="pim_ie_export_launch")
     * @Template()
     *
     * @return array
     */
    public function launchAction(Job $job)
    {
    }
}
