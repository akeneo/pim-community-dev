<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Pim\Bundle\ImportExportBundle\JobTemplate\JobTemplateProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Job Profile controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileController
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

    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    protected $templating;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var JobTemplateProviderInterface */
    protected $jobTemplateProvider;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param ValidatorInterface           $validator
     * @param EventDispatcherInterface     $eventDispatcher
     * @param ConnectorRegistry            $connectorRegistry
     * @param JobInstanceType              $jobInstanceType
     * @param JobInstanceFactory           $jobInstanceFactory
     * @param JobLauncherInterface         $simpleJobLauncher
     * @param EntityManagerInterface       $entityManager
     * @param JobInstanceRepository        $jobInstanceRepository
     * @param TokenStorageInterface        $tokenStorage
     * @param JobTemplateProviderInterface $jobTemplateProvider
     * @param string                       $jobType
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        ConnectorRegistry $connectorRegistry,
        JobInstanceType $jobInstanceType,
        JobInstanceFactory $jobInstanceFactory,
        JobLauncherInterface $simpleJobLauncher,
        EntityManagerInterface $entityManager,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        JobTemplateProviderInterface $jobTemplateProvider,
        $jobType
    ) {
        $this->connectorRegistry     = $connectorRegistry;
        $this->jobType               = $jobType;

        $this->jobInstanceType       = $jobInstanceType;
        $this->jobInstanceType->setJobType($this->jobType);

        $this->jobInstanceFactory    = $jobInstanceFactory;
        $this->simpleJobLauncher     = $simpleJobLauncher;
        $this->formFactory           = $formFactory;
        $this->request               = $request;
        $this->router                = $router;
        $this->templating            = $templating;
        $this->eventDispatcher       = $eventDispatcher;
        $this->validator             = $validator;
        $this->entityManager         = $entityManager;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobTemplateProvider   = $jobTemplateProvider;
        $this->tokenStorage          = $tokenStorage;
    }

    /**
     * Create a job instance
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $jobInstance = $this->jobInstanceFactory->createJobInstance($this->getJobType());
        $form = $this->formFactory->create($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $jobParamFactory = new JobParametersFactory();
                $jobParameters = $jobParamFactory->createDefault($jobInstance->getJob());
                $jobInstance->setRawConfiguration($jobParameters->getParameters());

                $this->entityManager->persist($jobInstance);
                $this->entityManager->flush();

                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.created', $this->getJobType())));

                $url = $this->router->generate(
                    sprintf('pim_importexport_%s_profile_edit', $this->getJobType()),
                    ['id' => $jobInstance->getId()]
                );
                $response = ['status' => 1, 'url' => $url];

                return new Response(json_encode($response));
            }
        }

        return $this->templating->renderResponse(
            $this->jobTemplateProvider->getCreateTemplate($jobInstance),
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
     * @return Response
     */
    public function showAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_SHOW, new GenericEvent($jobInstance));

        $form = $this->formFactory->create($this->jobInstanceType, $jobInstance, ['disabled' => true]);
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

        return $this->templating->renderResponse(
            $this->jobTemplateProvider->getShowTemplate($jobInstance),
            [
                'form'             => $form->createView(),
                'jobInstance'      => $jobInstance,
                'violations'       => $this->validator->validate($jobInstance, ['Default', 'Execution']),
                'uploadViolations' => $this->validator->validate($jobInstance, ['Default', 'UploadExecution']),
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
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EDIT, new GenericEvent($jobInstance));

        $form = $this->formFactory->create($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->entityManager->persist($jobInstance);
                $this->entityManager->flush();

                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.updated', $this->getJobType())));

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EDIT, new GenericEvent($jobInstance));

        return $this->templating->renderResponse(
            $this->jobTemplateProvider->getEditTemplate($jobInstance),
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
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response('', 404);
            }

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_REMOVE, new GenericEvent($jobInstance));

        $this->entityManager->remove($jobInstance);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        }

        return $this->redirectToIndexView();
    }

    /**
     * Launch a job from uploaded file
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function launchUploadedAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

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
     * @return RedirectResponse
     */
    public function launchAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

            return $this->redirectToIndexView();
        }

        if ($this->validate($jobInstance)) {
            $jobExecution = $this->launchJob(false, $jobInstance);

            return $this->redirectToReportView($jobExecution->getId());
        }

        return $this->redirectToShowView($id);
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
        $violations = $this->validator->validate($jobInstance, ['Default', 'Execution']);

        return 0 === $violations->count();
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
        $uploadViolations = $this->validator->validate($jobInstance, ['Default', 'UploadExecution']);

        if (0 === $uploadViolations->count()) {
            $fileInfo = $this->getFileInfo();
            if (null === $fileInfo) {
                return false;
            }

            return $this->configureUploadJob($jobInstance, $fileInfo);
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

        $configuration = $jobInstance->getJob()->getConfiguration();
        $configuration['send_email'] = true;
        $jobExecution = $this->simpleJobLauncher
            ->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $configuration);

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EXECUTE, new GenericEvent($jobInstance));

        $this->request->getSession()->getFlashBag()
            ->add('success', new Message(sprintf('flash.%s.running', $this->getJobType())));

        return $jobExecution;
    }

    /**
     * Get uploaded file
     *
     * @return FileInfoInterface|null
     */
    protected function getFileInfo()
    {
        if ($this->request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($this->request);
            if ($form->isValid()) {
                $fileInfo = $form->get('file')->getData();
                if (null !== $fileInfo && null !== $fileInfo->getUploadedFile()) {
                    return $fileInfo;
                }

                $this->request->getSession()->getFlashBag()
                    ->add('error', new Message('You must select a file to upload'));
            }
        }

        return null;
    }

    /**
     * Configure job instance for uploaded file
     *
     * @param JobInstance       $jobInstance
     * @param FileInfoInterface $fileInfo
     *
     * @return bool
     */
    protected function configureUploadJob(JobInstance $jobInstance, FileInfoInterface $fileInfo)
    {
        $success = false;

        $job = $jobInstance->getJob();
        $uploadedFile = $fileInfo->getUploadedFile();
        $file = $uploadedFile->move(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());

        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();

                if ($reader instanceof UploadedFileAwareInterface) {
                    $constraints = $reader->getUploadedFileConstraints();
                    $this->fileError = $this->validator->validate($fileInfo, $constraints);

                    if ($this->fileError->count() !== 0) {
                        foreach ($this->fileError as $error) {
                            $this->request->getSession()->getFlashBag()
                                ->add('error', new Message($error->getMessage()));
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
     * @return JobInstance|RedirectResponse
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->jobInstanceRepository->find($id);

        if (null === $jobInstance) {
            throw new NotFoundHttpException('Akeneo\Component\Batch\Model\JobInstance entity not found');
        }

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw new NotFoundHttpException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->connectorRegistry->getJob($jobInstance);

        if (!$job) {
            throw new NotFoundHttpException(
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
     * @return RedirectResponse
     */
    protected function redirectToIndexView()
    {
        return new RedirectResponse(
            $this->router->generate((sprintf('pim_importexport_%s_profile_index', $this->getJobType())))
        );
    }

    /**
     * Redirect to the show view
     *
     * @param int $jobId
     *
     * @return RedirectResponse
     */
    protected function redirectToShowView($jobId)
    {
        return new RedirectResponse($this->router->generate(
            sprintf('pim_importexport_%s_profile_show', $this->getJobType()),
            ['id' => $jobId]
        ));
    }

    /**
     * Redirect to the report view
     *
     * @param int $jobId
     *
     * @return RedirectResponse
     */
    protected function redirectToReportView($jobId)
    {
        return new RedirectResponse($this->router->generate(
            sprintf('pim_importexport_%s_execution_show', $this->getJobType()),
            ['id' => $jobId]
        ));
    }

    /**
     * Create file upload form
     *
     * @return Form
     */
    protected function createUploadForm()
    {
        return $this->formFactory->create(new UploadType(), null, ['validation_groups' => ['upload']]);
    }
}
