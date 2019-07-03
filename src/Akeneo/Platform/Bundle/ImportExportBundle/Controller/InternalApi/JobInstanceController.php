<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use League\Flysystem\FilesystemInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * JobInstance rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var NormalizerInterface */
    protected $jobInstanceNormalizer;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var JobParametersValidator */
    protected $jobParameterValidator;

    /** @var JobParametersFactory */
    protected $jobParamsFactory;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var RouterInterface */
    protected $router;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var JobInstanceFactory */
    protected $jobInstanceFactory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CollectionFilterInterface */
    protected $inputFilter;

    /** @var FilesystemInterface */
    protected $filesystem;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param JobRegistry                           $jobRegistry
     * @param NormalizerInterface                   $jobInstanceNormalizer
     * @param ObjectUpdaterInterface                $updater
     * @param SaverInterface                        $saver
     * @param RemoverInterface                      $remover
     * @param ValidatorInterface                    $validator
     * @param JobParametersValidator                $jobParameterValidator
     * @param JobParametersFactory                  $jobParamsFactory
     * @param JobLauncherInterface                  $jobLauncher
     * @param TokenStorageInterface                 $tokenStorage
     * @param RouterInterface                       $router
     * @param FormProviderInterface                 $formProvider
     * @param ObjectFilterInterface                 $objectFilter
     * @param NormalizerInterface                   $constraintViolationNormalizer
     * @param JobInstanceFactory                    $jobInstanceFactory
     * @param EventDispatcherInterface              $eventDispatcher
     * @param CollectionFilterInterface             $inputFilter
     * @param string                                $uploadTmpDir
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        JobRegistry $jobRegistry,
        NormalizerInterface $jobInstanceNormalizer,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        ValidatorInterface $validator,
        JobParametersValidator $jobParameterValidator,
        JobParametersFactory $jobParamsFactory,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        FormProviderInterface $formProvider,
        ObjectFilterInterface $objectFilter,
        NormalizerInterface $constraintViolationNormalizer,
        JobInstanceFactory $jobInstanceFactory,
        EventDispatcherInterface $eventDispatcher,
        CollectionFilterInterface $inputFilter,
        FilesystemInterface $filesystem
    ) {
        $this->repository            = $repository;
        $this->jobRegistry           = $jobRegistry;
        $this->jobInstanceNormalizer = $jobInstanceNormalizer;
        $this->updater               = $updater;
        $this->saver                 = $saver;
        $this->remover               = $remover;
        $this->validator             = $validator;
        $this->jobParameterValidator = $jobParameterValidator;
        $this->jobParamsFactory      = $jobParamsFactory;
        $this->jobLauncher           = $jobLauncher;
        $this->tokenStorage          = $tokenStorage;
        $this->router                = $router;
        $this->formProvider          = $formProvider;
        $this->objectFilter          = $objectFilter;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->jobInstanceFactory    = $jobInstanceFactory;
        $this->eventDispatcher       = $eventDispatcher;
        $this->inputFilter           = $inputFilter;
        $this->filesystem            = $filesystem;
    }

    /**
     * Get an import job profile
     *
     * @param string $identifier
     *
     * @AclAncestor("pim_importexport_import_profile_show")
     *
     * @return JsonResponse
     */
    public function getImportAction($identifier)
    {
        return $this->getAction($identifier);
    }

    /**
     * Get an export job profile
     *
     * @param string $identifier
     *
     * @AclAncestor("pim_importexport_export_profile_show")
     *
     * @return JsonResponse
     */
    public function getExportAction($identifier)
    {
        return $this->getAction($identifier);
    }

    /**
     * Edit an import job profile
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @AclAncestor("pim_importexport_import_profile_edit")
     *
     * @return Response
     */
    public function putImportAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->putAction($request, $identifier);
    }

    /**
     * Edit an export job profile
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @AclAncestor("pim_importexport_export_profile_edit")
     *
     * @return Response
     */
    public function putExportAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->putAction($request, $identifier);
    }

    /**
     * Delete an export job profile
     *
     * @param string $code
     *
     * @AclAncestor("pim_importexport_import_profile_remove")
     *
     * @return Response
     */
    public function deleteImportAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->deleteAction($code);
    }

    /**
     * Delete an export job profile
     *
     * @param string $code
     *
     * @AclAncestor("pim_importexport_export_profile_remove")
     *
     * @return Response
     */
    public function deleteExportAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->deleteAction($code);
    }

    /**
     * Launch an import job
     *
     * @param Request $request
     * @param string  $code
     *
     * @AclAncestor("pim_importexport_import_profile_launch")
     *
     * @return Response
     */
    public function launchImportAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->launchAction($request, $code);
    }

    /**
     * Launch an export job
     *
     * @param Request $request
     * @param string  $code
     *
     * @AclAncestor("pim_importexport_export_profile_launch")
     *
     * @return Response
     */
    public function launchExportAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->launchAction($request, $code);
    }

    /**
     * Get a job profile
     *
     * @param string $identifier
     *
     * @return JsonResponse
     */
    protected function getAction($identifier)
    {
        $jobInstance = $this->getJobInstance($identifier);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.show')) {
            throw new AccessDeniedHttpException();
        }

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * Edit a job profile
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     */
    protected function putAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $jobInstance = $this->getJobInstance($identifier);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.edit')) {
            throw new AccessDeniedHttpException();
        }

        $data = json_decode($request->getContent(), true);
        $filteredData = $this->inputFilter->filterCollection(
            $data,
            'pim.internal_api.job_instance.edit',
            ['preserve_keys' => true]
        );
        $this->updater->update($jobInstance, $filteredData);

        $errors = $this->getValidationErrors($jobInstance);
        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $this->saver->save($jobInstance);

        $this->eventDispatcher->dispatch(
            JobInstanceEvents::POST_SAVE,
            new GenericEvent($jobInstance, ['data' => $data])
        );

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * Delete a job profile
     *
     * @param string $code
     *
     * @return Response
     */
    protected function deleteAction($code): Response
    {
        $jobInstance = $this->getJobInstance($code);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.delete')) {
            throw new AccessDeniedHttpException();
        }

        $this->remover->remove($jobInstance);

        return new JsonResponse(null, 204);
    }

    /**
     * Launch a job
     *
     * @param Request $request
     * @param string  $code
     *
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    protected function launchAction(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $jobInstance = $this->getJobInstance($code);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.execute')) {
            throw new AccessDeniedHttpException();
        }

        $file = $request->files->get('file');
        if (null === $file && $this->isFileUpload($request)) {
            return new JsonResponse(['message' => 'pim_import_export.entity.import_profile.flash.upload.error'], 400);
        }

        if (null !== $file) {
            if (UPLOAD_ERR_OK !== $file->getError()) {
                return new JsonResponse(['message' => 'pim_import_export.entity.import_profile.flash.upload.error'], 400);
            }

            $violations = $this->validator->validate($file);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = [
                        'message' => $violation->getMessage(),
                        'invalid_value' => $violation->getInvalidValue()
                    ];
                }

                return new JsonResponse($errors, 400);
            }

            $jobFileLocation = new JobFileLocation($code.DIRECTORY_SEPARATOR. $file->getClientOriginalName(), true);

            if ($this->filesystem->has($jobFileLocation->path())) {
                $this->filesystem->delete($jobFileLocation->path());
            }

            $fileContent = fopen($file->getPathname(), 'r');
            $this->filesystem->writeStream($jobFileLocation->path(), $fileContent);
            if (is_resource($fileContent)) {
                fclose($fileContent);
            }

            $rawParameters = $jobInstance->getRawParameters();
            $rawParameters['filePath'] = $jobFileLocation->url();
            $jobInstance->setRawParameters($rawParameters);
        }

        $validationGroups = null !== $file ? ['Default', 'Execution', 'UploadExecution'] : ['Default', 'Execution'];
        $errors = $this->getValidationErrors($jobInstance, $validationGroups);
        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $jobExecution = $this->launchJob($jobInstance);

        return new JsonResponse([
            'redirectUrl' => '#' . $this->router->generate(
                sprintf('pim_importexport_%s_execution_show', $jobInstance->getType()),
                ['id' => $jobExecution->getId()]
            )
        ], 200);
    }

    /**
     * Get a job instance
     *
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return JobInstance
     */
    protected function getJobInstance($code)
    {
        $jobInstance = $this->repository->findOneByIdentifier($code);
        if (null === $jobInstance) {
            throw new NotFoundHttpException(sprintf('%s entity not found', JobInstance::class));
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());

        if (null === $job) {
            throw new NotFoundHttpException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $jobInstance->getType(),
                    $jobInstance->getConnector(),
                    $jobInstance->getType(),
                    $jobInstance->getJobName()
                )
            );
        }

        return $jobInstance;
    }

    /**
     * Get an array of job names
     *
     * @param Request $request
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getJobNamesAction(Request $request)
    {
        $jobType = $request->query->get('jobType');
        $choices = [];
        foreach ($this->jobRegistry->allByTypeGroupByConnector($jobType) as $connector => $jobs) {
            foreach ($jobs as $key => $job) {
                $choices[$connector][$key] = $job->getName();
            }
        }

        return new JsonResponse($choices);
    }

    /**
     * Aggregate validation errors
     *
     * @param JobInstance $jobInstance
     * @param array|null  $groups
     *
     * @return array
     */
    protected function getValidationErrors(JobInstance $jobInstance, $groups = null)
    {
        $rawParameters = $jobInstance->getRawParameters();
        $parametersViolations = [];
        if (!empty($rawParameters)) {
            $job = $this->jobRegistry->get($jobInstance->getJobName());
            $parameters = $this->jobParamsFactory->create($job, $rawParameters);
            $parametersViolations = $this->jobParameterValidator->validate($job, $parameters, $groups);
        }

        $errors = [];
        $accessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
        if (count($parametersViolations) > 0) {
            foreach ($parametersViolations as $error) {
                $accessor->setValue($errors, '[configuration]' . $error->getPropertyPath(), $error->getMessage());
            }
        }

        $globalViolations = $this->validator->validate($jobInstance, new Valid(), ['Default']);
        if ($globalViolations->count() > 0) {
            foreach ($globalViolations as $error) {
                $errors[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Normalize the job errors
     *
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    protected function normalizeJobInstance(JobInstance $jobInstance)
    {
        $normalizedJobInstance = $this->jobInstanceNormalizer->normalize($jobInstance, 'standard');

        return array_merge($normalizedJobInstance, [
            'meta' => [
                'form' => $this->formProvider->getForm($jobInstance),
                'id'   => $jobInstance->getId()
            ]
        ]);
    }

    /**
     * Allow to validate and run the job
     *
     * @param JobInstance $jobInstance
     *
     * @return JobExecution
     */
    protected function launchJob(JobInstance $jobInstance) : JobExecution
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = $jobInstance->getRawParameters();
        $configuration['send_email'] = true;
        $configuration['user_to_notify'] = $user->getUsername();

        return $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }

    /**
     * Create an import profile
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createImportAction(Request $request)
    {
        return $this->createAction($request, 'import');
    }

    /**
     * Create an export profile
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createExportAction(Request $request)
    {
        return $this->createAction($request, 'export');
    }

    /**
     * Create a job profile with a given type
     *
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     */
    protected function createAction(Request $request, string $type)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        $jobInstance = $this->jobInstanceFactory->createJobInstance($type);
        $this->updater->update($jobInstance, $data);

        $violations = $this->validator->validate($jobInstance);
        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['jobInstance' => $jobInstance]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $job = $this->jobRegistry->get($jobInstance->getJobName());
        $jobParameters = $this->jobParamsFactory->create($job);
        $jobInstance->setRawParameters($jobParameters->all());
        $this->saver->save($jobInstance);

        $this->eventDispatcher->dispatch(
            JobInstanceEvents::POST_SAVE,
            new GenericEvent($jobInstance, ['data' => $data])
        );

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * This is the only way we found to test that a file too big was uploaded.
     * If the file size exceeds the server limit, a warning is thrown on the apache container logs
     * and the request does not contain any information that a file was uploaded on the FPM side.
     * This happens only when the upload exceeds 'post_max_size' and we can detect it by having a positive
     * Content-Length header corresponding to the file length that was sent.
     */
    private function isFileUpload(Request $request): bool
    {
        return $request->server->get('CONTENT_LENGTH') > 0;
    }
}
