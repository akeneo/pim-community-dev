<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\ProductBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pim\Bundle\ImportExportBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Job controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class JobController extends Controller
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
            $view = $this->getIndexLogicName();
        }

        return $this->render(
            $view,
            array(
                'datagrid' => $datagridView,
                'connectors' => $this->getJobs()
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
            $this->addFlash('error', 'Fail to create an job definition with an unknown job.');

            return $this->redirectIndex();
        }
        $job->setJobDefinition($jobDefinition);

        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash(
                    'success',
                    sprintf('The %s job has been successfully created.', $this->getJobType())
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
        $job = $this->getJob($id);

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
        $job  = $this->getJob($id);
        $form = $this->createForm(new JobType(), $job);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($job);

                $this->addFlash(
                    'success',
                    sprintf('The %s job has been successfully updated.', $this->getJobType())
                );

                return $this->redirectToShowView($job->getId());
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * {@inheritdoc}
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Method("DELETE")
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

            return $this->redirectToIndexView();
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
     * Redirect to the index view
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndexView()
    {
        return $this->redirect($this->getIndexLogicName());
    }

    /**
     * Get the index action logic name
     *
     * @abstract
     * @return string
     */
    abstract protected function getIndexLogicName();

    /**
     * Get jobs
     *
     * @abstract
     * @return array
     */
    abstract protected function getJobs();

    /**
     * Get the datagrid manager
     *
     * @abstract
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    abstract protected function getDatagridManager();
}
