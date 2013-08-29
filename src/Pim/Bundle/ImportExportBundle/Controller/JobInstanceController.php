<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\ProductBundle\Controller\Controller;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Pim\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\BatchBundle\Item\UploadedFileAwareInterface;

/**
 * Job Instance controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceController extends Controller
{
    /**
     * List the jobs instances
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
     * Create a job instance
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

        $jobInstance = new JobInstance($connector, $this->getJobType(), $alias);
        if (!$job = $registry->getJob($jobInstance)) {
            $this->addFlash(
                'error',
                sprintf('Failed to create an %s with an unknown job definition.', $this->getJobType())
            );

            return $this->redirectToIndexView();
        }
        $jobInstance->setJob($job);

        $form = $this->createForm(new JobInstanceType(), $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($jobInstance);

                $this->addFlash(
                    'success',
                    sprintf('The %s has been successfully created.', $this->getJobType())
                );

                return $this->redirectToShowView($jobInstance->getId());
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
     * Show a job instance
     *
     * @param integer $id
     *
     * @return template
     */
    public function showAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $uploadAllowed = false;
        $form = null;
        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            $reader = $step->getReader();
            if ($reader instanceof UploadedFileAwareInterface) {
                $uploadAllowed = true;
                $form = $this->createUploadForm()->createView();
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%s:show.html.twig', ucfirst($this->getJobType())),
            array(
                'jobInstance'   => $jobInstance,
                'violations'    => $this->getValidator()->validate($jobInstance, array('Default', 'Execution')),
                'uploadAllowed' => $uploadAllowed,
                'form'          => $form,
            )
        );
    }

    /**
     * Edit a job instance
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function editAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }
        $form = $this->createForm(new JobInstanceType(), $jobInstance);

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($jobInstance);

                $this->addFlash(
                    'success',
                    sprintf('The %s has been successfully updated.', $this->getJobType())
                );

                return $this->redirectToShowView($jobInstance->getId());
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
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->remove($jobInstance);

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
     * @param Request $request
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function launchAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        if (count($this->getValidator()->validate($jobInstance, array('Default', 'Execution'))) === 0) {
            $jobExecution = new JobExecution;
            $jobExecution->setJobInstance($jobInstance);
            $job = $jobInstance->getJob();

            if ($request->isMethod('POST')) {
                $form = $this->createUploadForm();
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $media = $data['file'];
                    $file = $media->getFile();

                    foreach ($job->getSteps() as $step) {
                        $reader = $step->getReader();

                        if ($reader instanceof UploadedFileAwareInterface) {
                            $constraints = $reader->getUploadedFileConstraints();
                            $errors = $this->getValidator()->validateValue($file, $constraints);

                            if (!empty($errors)) {
                                foreach ($errors as $error) {
                                    $this->addFlash('error', $error->getMessage());
                                }

                                return $this->redirectToShowView($jobInstance->getId());
                            }

                            $reader->setUploadedFile($file);
                        }
                    }
                }
            }

            $job->execute($jobExecution);

            if (ExitStatus::COMPLETED === $jobExecution->getExitStatus()->getExitCode()) {
                $this->addFlash('success', sprintf('The %s has been successfully executed.', $this->getJobType()));
            } else {
                $this->addFlash('error', sprintf('An error occured during the %s execution.', $this->getJobType()));
            }
        }

        return $this->redirectToShowView($jobInstance->getId());
    }

    /**
     * Get a job instance
     *
     * @param integer $id
     * @param boolean $checkStatus
     *
     * @return Job|RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->findOr404('PimBatchBundle:JobInstance', $id);

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->getConnectorRegistry()->getJob($jobInstance);

        if (!$job) {
            throw $this->createNotFoundException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $this->getJobType(),
                    $jobInstance->getConnector(),
                    $jobInstance->getType(),
                    $jobInstance->getAlias()
                )
            );
        }
        $jobInstance->setJob($job);

        return $jobInstance;
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

    /**
     * Create file upload form
     *
     * @return Form
     */
    protected function createUploadForm()
    {
        return $this->createFormBuilder()
            ->add('file', 'oro_media')
            ->getForm();
    }
}
