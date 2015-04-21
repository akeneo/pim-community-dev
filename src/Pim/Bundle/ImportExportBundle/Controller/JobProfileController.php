<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Pim\Bundle\ImportExportBundle\Factory\JobInstanceFactory;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Pim\Bundle\ImportExportBundle\Manager\JobManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Job Profile controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileController extends AbstractDoctrineController
{
    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /** @var string */
    protected $jobType;

    /** @var JobInstanceType */
    protected $jobInstanceType;

    /** @var JobInstanceFactory */
    protected $jobInstanceFactory;

    /** @var ConstraintViolationListInterface  */
    protected $fileError;

    /** @var JobManager */
    protected $jobManager;

    /** @var SimpleJobLauncher */
    protected $simpleJobLauncher;

    /** @var File */
    protected $file;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param ConnectorRegistry        $connectorRegistry
     * @param string                   $jobType
     * @param JobInstanceType          $jobInstanceType
     * @param JobInstanceFactory       $jobInstanceFactory
     * @param JobManager               $jobManager
     * @param SimpleJobLauncher        $simpleJobLauncher
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        ConnectorRegistry $connectorRegistry,
        $jobType,
        JobInstanceType $jobInstanceType,
        JobInstanceFactory $jobInstanceFactory,
        JobManager $jobManager,
        SimpleJobLauncher $simpleJobLauncher
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->connectorRegistry  = $connectorRegistry;
        $this->jobType            = $jobType;

        $this->jobInstanceType    = $jobInstanceType;
        $this->jobInstanceType->setJobType($this->jobType);

        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->jobManager         = $jobManager;
        $this->simpleJobLauncher  = $simpleJobLauncher;
    }

    /**
     * Create a job instance
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $jobInstance = $this->jobInstanceFactory->createJobInstance(null, $this->getJobType(), null);
        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->jobManager->save($jobInstance);

                $this->addFlash('success', sprintf('flash.%s.created', $this->getJobType()));

                $url = $this->generateUrl(
                    sprintf('pim_importexport_%s_profile_edit', $this->getJobType()),
                    ['id' => $jobInstance->getId()]
                );
                $response = ['status' => 1, 'url' => $url];

                return new Response(json_encode($response));
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%sProfile:create.html.twig', ucfirst($this->getJobType())),
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Show a job instance
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_SHOW, new GenericEvent($jobInstance));

        $form = $this->createForm($this->jobInstanceType, $jobInstance, ['disabled' => true]);
        $uploadAllowed = false;
        $uploadForm = null;
        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();
                if ($reader instanceof UploadedFileAwareInterface) {
                    $uploadAllowed = true;
                    $uploadForm = $this->createUploadForm()->createView();
                }
            }
        }

        $validator = $this->getValidator();

        if (null === $template = $job->getShowTemplate()) {
            $template = sprintf('PimImportExportBundle:%sProfile:show.html.twig', ucfirst($this->getJobType()));
        }

        return $this->render(
            $template,
            [
                'form'             => $form->createView(),
                'jobInstance'      => $jobInstance,
                'violations'       => $validator->validate($jobInstance, ['Default', 'Execution']),
                'uploadViolations' => $validator->validate($jobInstance, ['Default', 'UploadExecution']),
                'uploadAllowed'    => $uploadAllowed,
                'uploadForm'       => $uploadForm,
            ]
        );
    }

    /**
     * Edit a job instance
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EDIT, new GenericEvent($jobInstance));

        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->jobManager->save($jobInstance);

                $this->addFlash(
                    'success',
                    sprintf('flash.%s.updated', $this->getJobType())
                );

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EDIT, new GenericEvent($jobInstance));

        if (null === $template = $jobInstance->getJob()->getEditTemplate()) {
            $template = sprintf('PimImportExportBundle:%sProfile:edit.html.twig', ucfirst($this->getJobType()));
        }

        return $this->render(
            $template,
            [
                'jobInstance' => $jobInstance,
                'form'        => $form->createView(),
            ]
        );
    }

    /**
     * Remove a job
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response('', 404);
            } else {
                return $this->redirectToIndexView();
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_REMOVE, new GenericEvent($jobInstance));

        $this->jobManager->remove($jobInstance);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToIndexView();
        }
    }

    /**
     * Validate if the job is correct or not
     *
     * @param JobInstance $jobInstance
     *
     * @return bool
     */
    protected function validate(JobInstance $jobInstance)
    {
        $violations = $this->getValidator()->validate($jobInstance, ['Default', 'Execution']);

        return $violations->count() === 0;
    }

    /**
     * Validate if the job is correct from an uploaded file
     *
     * @param JobInstance $jobInstance
     *
     * @return bool
     */
    protected function validateUpload(JobInstance $jobInstance)
    {
        $uploadViolations = $this->getValidator()->validate($jobInstance, ['Default', 'UploadExecution']);

        $uploadMode = $uploadViolations->count() === 0 ? $this->processUploadForm($jobInstance) : false;

        return $uploadMode && $this->configureUploadJob($jobInstance, $this->file);
    }

    /**
     * Launch a job from uploaded file
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function launchUploadedAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        if ($this->validateUpload($jobInstance)) {
            $jobExecution = $this->launchJob(true, $jobInstance);

            return $this->redirectToReportView($jobExecution->getId());
        }

        return $this->redirectToShowView($id);
    }

    /**
     * Launch a job
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function launchAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        if ($this->validate($jobInstance)) {
            $jobExecution = $this->launchJob(false, $jobInstance);

            return $this->redirectToReportView($jobExecution->getId());
        }

        return $this->redirectToShowView($id);
    }

    /**
     * Process the upload form
     *
     * @param JobInstance $jobInstance
     *
     * @return bool
     */
    protected function processUploadForm(JobInstance $jobInstance)
    {
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->get('file')->getData();
                if (null !== $file = $data->getFile()) {
                    $this->file = $file->move(sys_get_temp_dir(), $file->getClientOriginalName());

                    return true;
                }

                $this->addFlash('error', 'You must select a file to upload');
            }
        }

        return false;
    }

    /**
     * Allow to validate and run the job
     *
     * @param bool        $isUpload
     * @param JobInstance $jobInstance
     *
     * @return JobInstance
     */
    protected function launchJob($isUpload, JobInstance $jobInstance)
    {
        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EXECUTE, new GenericEvent($jobInstance));

        $jobExecution = $this->simpleJobLauncher->setConfig(['email' => true, 'upload' => $isUpload])
            ->launch($jobInstance, $this->getUser());

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EXECUTE, new GenericEvent($jobInstance));

        $this->addFlash('success', sprintf('The %s is running.', $this->getJobType()));

        return $jobExecution;
    }

    /**
     * Configure job instance for uploaded file
     *
     * @param JobInstance $jobInstance
     * @param File        $file
     *
     * @return bool
     */
    protected function configureUploadJob(JobInstance $jobInstance, File $file)
    {
        $success = false;

        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();

                if ($reader instanceof UploadedFileAwareInterface) {
                    $constraints = $reader->getUploadedFileConstraints();
                    $this->fileError = $this->getValidator()->validateValue($file, $constraints);

                    if ($this->fileError->count() !== 0) {
                        foreach ($this->fileError as $error) {
                            $this->addFlash('error', $error->getMessage());
                        }

                        return false;
                    } else {
                        $reader->setUploadedFile($file);
                        $success = true;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Get a job instance
     *
     * @param int  $id
     * @param bool $checkStatus
     *
     * @throws NotFoundHttpException
     *
     * @return Job|RedirectResponse
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->findOr404('AkeneoBatchBundle:JobInstance', $id);

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
        return $this->redirectToRoute(sprintf('pim_importexport_%s_profile_index', $this->getJobType()));
    }

    /**
     * Redirect to the show view
     *
     * @param int $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToShowView($jobId)
    {
        return $this->redirectToRoute(
            sprintf('pim_importexport_%s_profile_show', $this->getJobType()),
            ['id' => $jobId]
        );
    }

    /**
     * Redirect to the report view
     *
     * @param int $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToReportView($jobId)
    {
        return $this->redirectToRoute(
            sprintf('pim_importexport_%s_execution_show', $this->getJobType()),
            ['id' => $jobId]
        );
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
