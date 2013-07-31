<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Controller\Controller;
use Pim\Bundle\ImportExportBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Job\JobExecution;

/**
 * Job controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class JobControllerAbstract extends Controller
{
    const JOB_STATUS_READY = 0;

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
            $view = $this->getIndexLogicName();
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function createAction(Request $request)
    {
        $connector = $request->query->get('connector');
        $alias     = $request->query->get('alias');
        $registry  = $this->getConnectorRegistry();

        $job = new Job($connector, $this->getJobType(), $alias);
        if (!$jobDefinition = $registry->getJob($job)) {
            $this->addFlash('error', sprintf('Fail to create an %s with an unknown job.', $this->getJobType()));

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

        return array(
            'form'      => $form->createView(),
            'connector' => $connector,
            'alias'     => $alias,
        );
    }

    /**
     * Show a job
     *
     * @param integer $id
     *
     * @return array
     */
    public function showAction($id)
    {
        $job = $this->getJob($id, false);

        return array(
            'job'        => $job,
            'violations' => $this->getValidator()->validate($job),
        );
    }

    /**
     * Edit a job
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function editAction($id)
    {
        try {
            $job = $this->getJob($id);
        } catch (\NotFoundHttpException $e) {
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

        return array('form' => $form->createView());
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
        $job = $this->getJob($id);
        $this->remove($job);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Job successfully removed');

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
        } catch (\NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        // TODO || FIXME : Why ?
        // Ok the job can't be launch because invalid
        // But we mustn't return a 404 !!?
        if (count($this->getValidator()->validate($job)) > 0) {
            throw $this->createNotFoundException();
        }
        $jobExecution = new JobExecution;
        $definition = $job->getJobDefinition();
        $definition->execute($jobExecution);

        //TODO Analyse $jobExecution to define wether or not it was ok
        $this->addFlash('success', 'Job has been successfully executed.');

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

        if ($checkStatus && $job->getStatus() !== self::JOB_STATUS_READY) {
            $this->addFlash('error', sprintf('The job "%s" is currently in progress', $job->getLabel()));

            return $this->redirectToIndexView();
        }

        $jobDefinition = $this->getConnectorRegistry()->getJob($job);

        if (!$jobDefinition) {
            throw $this->createNotFoundException(
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
     * Redirect to the index view
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndexView()
    {
        return $this->redirectToRoute($this->getIndexRouteName());
    }

    /**
     * Return the job type of the controller
     *
     * @abstract
     * @return string
     */
    abstract protected function getJobType();

    /**
     * Redirect to the show view
     *
     * @param integer $jobId
     *
     * @abstract
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    abstract protected function redirectToShowView($jobId);

    /**
     * Get the index route name
     *
     * @abstract
     * @return string
     */
    abstract protected function getIndexRouteName();

    /**
     * Get the index action logic name
     *
     * @abstract
     * @return string
     */
    abstract protected function getIndexLogicName();

    /**
     * Get the datagrid manager
     *
     * @abstract
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    abstract protected function getDatagridManager();
}
