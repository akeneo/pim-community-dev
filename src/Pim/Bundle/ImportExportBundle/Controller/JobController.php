<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\ProductBundle\Controller\Controller;
use Pim\Bundle\ImportExportBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Job controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobController extends Controller
{
    /**
     * List the jobs
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $gridManager  = $this->getDatagridManager();
        $datagridView = $gridManager->getDatagrid()->createView();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = sprintf('PimImportExportBundle:%s:index.html.twig', ucfirst($this->getJobType()));
        }

        return $this->render(
            $view,
            array(
                'datagrid' => $datagridView,
                'connectors' => $this->getConnectorRegistry()->getJobs($this->getJobType())
            )
        );
    }

    /**
     * Create a job
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function createAction(Request $request)
    {
        $connector = $request->query->get('connector');
        $alias     = $request->query->get('alias');
        $registry  = $this->getConnectorRegistry();

        $job = new Job($connector, $this->getJobType(), $alias);
        if (!$jobDefinition = $registry->getJob($job)) {
            $this->addFlash(
                'error',
                sprintf('Failed to create an %s with an unknown job definition.', $this->getJobType())
            );

            return $this->redirectToIndexView();
        }
        $job->setJobDefinition($jobDefinition);

        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash(
                    'success',
                    sprintf('The %s has been successfully created.', $this->getJobType())
                );

                return $this->redirectToShowView($job->getId());
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%s:edit.html.twig', ucfirst($this->getJobType())),
            array(
                'form'      => $form->createView(),
                'connector' => $connector,
                'alias'     => $alias,
            )
        );
    }

    /**
     * Show a job
     *
     * @param integer $id
     *
     * @return template
     */
    public function showAction($id)
    {
        try {
            $job = $this->getJob($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        return $this->render(
            sprintf('PimImportExportBundle:%s:show.html.twig', ucfirst($this->getJobType())),
            array(
                'job'        => $job,
                'violations' => $this->getValidator()->validate($job, array('Default', 'Execution')),
            )
        );
    }

    /**
     * Edit a job
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function editAction($id)
    {
        try {
            $job = $this->getJob($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }
        $form = $this->createForm(new JobType(), $job);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash(
                    'success',
                    sprintf('The %s has been successfully updated.', $this->getJobType())
                );

                return $this->redirectToShowView($job->getId());
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%s:edit.html.twig', ucfirst($this->getJobType())),
            array(
                'form'      => $form->createView(),
            )
        );
    }

    /**
     * Remove a job
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        try {
            $job = $this->getJob($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->remove($job);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', sprintf('The %s has been successfully removed', $this->getJobType()));

            return $this->redirectToIndexView();
        }
    }

    /**
     * View report for a job
     *
     * @param integer $id
     */
    public function reportAction($id)
    {
    }

    /**
     * Launch a job
     *
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function launchAction($id)
    {
        try {
            $job = $this->getJob($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        if (count($this->getValidator()->validate($job, array('Default', 'Execution'))) === 0) {
            $jobExecution = new JobExecution;
            $jobExecution->setJob($job);
            $definition = $job->getJobDefinition();
            $definition->execute($jobExecution);

            if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
                $this->addFlash('success', sprintf('The %s has been successfully executed.', $this->getJobType()));
            } else {
                $this->addFlash('error', sprintf('An error occured during the %s execution.', $this->getJobType()));
            }
        }

        return $this->redirectToShowView($job->getId());
    }

    /**
     * Get a job
     *
     * @param integer $id
     * @param boolean $checkStatus
     *
     * @return Job|RedirectResponse
     *
     * @throw NotFoundHttpException
     */
    protected function getJob($id, $checkStatus = true)
    {
        $job = $this->findOr404('PimBatchBundle:Job', $id);

        if ($checkStatus && $job->getStatus() === Job::STATUS_IN_PROGRESS) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $job->getJobType(), $job->getLabel())
            );
        }

        $jobDefinition = $this->getConnectorRegistry()->getJob($job);

        if (!$jobDefinition) {
            throw $this->createNotFoundException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $this->getJobType(),
                    $job->getConnector(),
                    $job->getType(),
                    $job->getAlias()
                )
            );
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

    /**
     * Return the job type of the controller
     *
     * @return string
     */
    protected function getJobType()
    {
        return null;
    }

    /**
     * Redirect to the index view
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndexView()
    {
        return $this->redirectToRoute(sprintf('pim_importexport_%s_index', $this->getJobType()));
    }

    /**
     * Redirect to the show view
     *
     * @param integer $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToShowView($jobId)
    {
        return $this->redirectToRoute(sprintf('pim_importexport_%s_show', $this->getJobType()), array('id' => $jobId));
    }

    /**
     * Get the datagrid manager
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    protected function getDatagridManager()
    {
        $managerAlias = sprintf('pim_import_export.datagrid.manager.%s', $this->getJobType());

        return $this->get($managerAlias);
    }
}
