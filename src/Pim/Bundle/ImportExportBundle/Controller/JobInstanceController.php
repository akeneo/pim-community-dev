<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Pim\Bundle\CatalogBundle\Form\Type\UploadType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\ExitStatus;
use Oro\Bundle\BatchBundle\Item\UploadedFileAwareInterface;

/**
 * Job Instance controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var ConnectorRegistry
     */
    private $connectorRegistry;

    /**
     * @var string
     */
    private $jobType;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param RegistryInterface        $doctrine
     * @param DatagridWorkerInterface  $datagridWorker
     * @param ConnectorRegistry        $connectorRegistry
     * @param string                   $jobType
     * @param string                   $rootDir
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $datagridWorker,
        ConnectorRegistry $connectorRegistry,
        $jobType,
        $rootDir
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->datagridWorker    = $datagridWorker;
        $this->connectorRegistry = $connectorRegistry;
        $this->jobType           = $jobType;
        $this->rootDir           = $rootDir;
    }
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
                'connectors' => $this->connectorRegistry->getJobs($this->getJobType())
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

        $jobInstance = new JobInstance($connector, $this->getJobType(), $alias);
        if (!$job = $this->connectorRegistry->getJob($jobInstance)) {
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
                $this->getManager()->persist($jobInstance);
                $this->getManager()->flush();

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

        $validator = $this->getValidator();

        return $this->render(
            sprintf('PimImportExportBundle:%s:show.html.twig', ucfirst($this->getJobType())),
            array(
                'jobInstance'      => $jobInstance,
                'violations'       => $validator->validate($jobInstance, array('Default', 'Execution')),
                'uploadViolations' => $validator->validate($jobInstance, array('Default', 'UploadExecution')),
                'uploadAllowed'    => $uploadAllowed,
                'form'             => $form,
            )
        );
    }

    /**
     * Edit a job instance
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function editAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }
        $form = $this->createForm(new JobInstanceType(), $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getManager()->persist($jobInstance);
                $this->getManager()->flush();

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
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->getManager()->remove($jobInstance);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', sprintf('The %s has been successfully removed', $this->getJobType()));

            return $this->redirectToIndexView();
        }
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

        $violations       = $this->getValidator()->validate($jobInstance, array('Default', 'Execution'));
        $uploadViolations = $this->getValidator()->validate($jobInstance, array('Default', 'UploadExecution'));

        if (count($violations) === 0 || count($uploadViolations) === 0) {
            $jobExecution = new JobExecution();
            $jobExecution->setJobInstance($jobInstance);
            $job = $jobInstance->getJob();

            $uploadMode = false;
            if ($request->isMethod('POST') && count($uploadViolations) === 0) {
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

                            if ($errors->count()) {
                                foreach ($errors as $error) {
                                    $this->addFlash('error', $error->getMessage());
                                }

                                return $this->redirectToShowView($jobInstance->getId());
                            }

                            $reader->setUploadedFile($file);
                            $uploadMode = true;
                        }
                    }
                }
            }

            if ($uploadMode) {
                $job->execute($jobExecution);

            } else {
                $this->getManager()->persist($jobExecution);
                $this->getManager()->flush();
                $instanceCode = $jobExecution->getJobInstance()->getCode();
                $executionId = $jobExecution->getId();
                $cmd = sprintf('php %s/console oro:batch:job %s %s', $this->rootDir, $instanceCode, $executionId);
                $process = new Process($cmd);
                $process->start();
            }
            $this->addFlash('success', sprintf('The %s is running.', $this->getJobType()));
        }

        return $this->redirectToReportView($jobExecution->getId());
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
        $jobInstance = $this->findOr404('OroBatchBundle:JobInstance', $id);

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->connectorRegistry->getJob($jobInstance);

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
     * Return the job type of the controller
     *
     * @return string
     */
    protected function getJobType()
    {
        return $this->jobType;
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
     * Redirect to the report view
     *
     * @param integer $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToReportView($jobId)
    {
        return $this->redirectToRoute(
            sprintf('pim_importexport_%s_report_show', $this->getJobType()),
            array('id' => $jobId)
        );
    }

    /**
     * Get the datagrid manager
     *
     * @return \Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridManager
     */
    protected function getDatagridManager()
    {
        return $this->datagridWorker->getDatagridManager($this->getJobType(), 'pim_import_export');
    }

    /**
     * Create file upload form
     *
     * @return Form
     */
    protected function createUploadForm()
    {
        return $this->createForm(new UploadType());
    }
}
