<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
    /** @staticvar string */
    const DEFAULT_CREATE_TEMPLATE = 'PimImportExportBundle:%sProfile:create.html.twig';

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var string */
    protected $jobType;

    /** @var JobInstanceFormType */
    protected $jobInstanceFormType;

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

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var JobParametersValidator */
    protected $jobParametersValidator;

    /**
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param ValidatorInterface           $validator
     * @param EventDispatcherInterface     $eventDispatcher
     * @param JobRegistry                  $jobRegistry
     * @param JobInstanceFormType          $jobInstanceFormType
     * @param JobInstanceFactory           $jobInstanceFactory
     * @param JobLauncherInterface         $simpleJobLauncher
     * @param EntityManagerInterface       $entityManager
     * @param JobInstanceRepository        $jobInstanceRepository
     * @param TokenStorageInterface        $tokenStorage
     * @param JobParametersFactory         $jobParametersFactory
     * @param JobParametersValidator       $jobParametersValidator
     * @param string                       $jobType
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        JobRegistry $jobRegistry,
        JobInstanceFormType $jobInstanceFormType,
        JobInstanceFactory $jobInstanceFactory,
        JobLauncherInterface $simpleJobLauncher,
        EntityManagerInterface $entityManager,
        JobInstanceRepository $jobInstanceRepository,
        TokenStorageInterface $tokenStorage,
        JobParametersFactory $jobParametersFactory,
        JobParametersValidator $jobParametersValidator,
        $jobType
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobType = $jobType;

        $this->jobInstanceFormType = $jobInstanceFormType;
        $this->jobInstanceFormType->setJobType($this->jobType);

        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->templating = $templating;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->tokenStorage = $tokenStorage;
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
        $form = $this->formFactory->create($this->jobInstanceFormType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $job = $this->jobRegistry->get($jobInstance->getJobName());
                $jobParameters = $this->jobParametersFactory->create($job);
                $jobInstance->setRawParameters($jobParameters->all());

                $this->entityManager->persist($jobInstance);
                $this->entityManager->flush();

                $this->request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.created', $this->getJobType())));

                $url = $this->router->generate(
                    sprintf('pim_importexport_%s_profile_edit', $this->getJobType()),
                    ['code' => $jobInstance->getCode()]
                );
                $response = ['status' => 1, 'url' => $url];

                return new Response(json_encode($response));
            }
        }

        return $this->templating->renderResponse(
            sprintf(self::DEFAULT_CREATE_TEMPLATE, ucfirst($jobInstance->getType())),
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Show a job instance
     *
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     */
    public function showAction($code)
    {
        return $this->templating->renderResponse(
            'PimEnrichBundle:JobInstance:form.html.twig',
            [
                'jobInstanceIdentifier' => $code,
                'mode'                  => 'show'
            ]
        );
    }

    /**
     * Edit a job instance
     *
     * @param string $code
     *
     * @return Response
     */
    public function editAction($code)
    {
        return $this->templating->renderResponse(
            'PimEnrichBundle:JobInstance:form.html.twig',
            [
                'jobInstanceIdentifier' => $code,
                'mode'                  => 'edit'
            ]
        );
    }

    /**
     * Launch a job with an uploaded file
     *
     * @param string $code
     *
     * @return RedirectResponse
     */
    public function launchUploadedAction($code)
    {
        try {
            $jobInstance = $this->getJobInstance($code);
        } catch (NotFoundHttpException $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($e->getMessage()));

            return $this->redirectToIndexView();
        }

        $isConfigured = $this->configureWithUploadFile($jobInstance);
        $violations = $this->validateJobInstance($jobInstance, ['Default', 'UploadExecution']);

        if ($isConfigured && $violations->count() === 0) {
            $jobExecution = $this->launchJob($jobInstance);

            return $this->redirectToReportView($jobExecution->getId());
        }

        $this->addViolationFlashMessages($violations);

        return $this->redirectToShowView($code);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    protected function addViolationFlashMessages(ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {
            $this->request->getSession()->getFlashBag()->add('error', new Message($violation->getMessage()));
        }
    }

    /**
     * @param JobInstance $jobInstance
     * @param array       $validationGroups
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateJobInstance(JobInstance $jobInstance, array $validationGroups)
    {
        $rawParameters = $jobInstance->getRawParameters();
        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->jobParametersFactory->create($job, $rawParameters);

        /** @var ConstraintViolationListInterface $jobParamsViolations */
        $jobParamsViolations = $this->jobParametersValidator->validate(
            $job,
            $jobParameters,
            $validationGroups
        );

        /** @var ConstraintViolationListInterface $jobInstanceViolations */
        $jobInstanceViolations = $this->validator->validate($jobInstance, $validationGroups);
        foreach ($jobInstanceViolations as $violation) {
            $jobParamsViolations->add($violation);
        }

        return $jobParamsViolations;
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
     * Configure job instance with uploaded file, returns true if well configured
     *
     * @param JobInstance $jobInstance
     *
     * @return boolean
     */
    protected function configureWithUploadFile(JobInstance $jobInstance)
    {
        $fileInfo = $this->getFileInfo();
        if (null === $fileInfo) {
            return false;
        }

        $uploadedFile = $fileInfo->getUploadedFile();
        $file = $uploadedFile->move(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $rawParameters = $jobInstance->getRawParameters();
        $rawParameters['filePath'] = $file->getRealPath();
        $jobInstance->setRawParameters($rawParameters);

        return true;
    }

    /**
     * Get a job instance
     *
     * @param string $code
     * @param bool   $checkStatus
     *
     * @throws NotFoundHttpException
     *
     * @return JobInstance|RedirectResponse
     */
    protected function getJobInstance($code, $checkStatus = true)
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($code);

        if (null === $jobInstance) {
            throw new NotFoundHttpException('Akeneo\Component\Batch\Model\JobInstance entity not found');
        }

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw new NotFoundHttpException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());

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
                    $jobInstance->getJobName()
                )
            );
        }

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
     * @param string $code
     *
     * @return RedirectResponse
     */
    protected function redirectToShowView($code)
    {
        return new RedirectResponse($this->router->generate(
            sprintf('pim_importexport_%s_profile_show', $this->getJobType()),
            ['code' => $code]
        ));
    }

    /**
     * Redirect to the report view
     *
     * @param int $jobExecutionId
     *
     * @return RedirectResponse
     */
    protected function redirectToReportView($jobExecutionId)
    {
        return new RedirectResponse($this->router->generate(
            sprintf('pim_importexport_%s_execution_show', $this->getJobType()),
            ['id' => $jobExecutionId]
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
