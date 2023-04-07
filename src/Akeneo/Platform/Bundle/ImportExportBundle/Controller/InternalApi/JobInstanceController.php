<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Event\JobInstanceEvents;
use Akeneo\Platform\Bundle\ImportExportBundle\Exception\JobInstanceCannotBeUpdatedException;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry;
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
use League\Flysystem\FilesystemOperator;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * JobInstance rest controller.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceController
{
    public function __construct(
        private IdentifiableObjectRepositoryInterface $repository,
        private JobRegistry $jobRegistry,
        private NormalizerInterface $jobInstanceNormalizer,
        private ObjectUpdaterInterface $updater,
        private SaverInterface $saver,
        private RemoverInterface $remover,
        private ValidatorInterface $validator,
        private JobParametersValidator $jobParameterValidator,
        private JobParametersFactory $jobParamsFactory,
        private JobLauncherInterface $jobLauncher,
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router,
        private FormProviderInterface $formProvider,
        private ObjectFilterInterface $objectFilter,
        private NormalizerInterface $constraintViolationNormalizer,
        private JobInstanceFactory $jobInstanceFactory,
        private EventDispatcherInterface $eventDispatcher,
        private CollectionFilterInterface $inputFilter,
        private FilesystemOperator $filesystem,
        private SecurityFacade $securityFacade,
        private CredentialsEncrypterRegistry $credentialsEncrypterRegistry,
    ) {
    }

    /**
     * @AclAncestor("pim_importexport_import_profile_show")
     */
    public function getImportAction(string $identifier): JsonResponse
    {
        return $this->getAction($identifier);
    }

    /**
     * @AclAncestor("pim_importexport_export_profile_show")
     */
    public function getExportAction(string $identifier): JsonResponse
    {
        return $this->getAction($identifier);
    }

    /**
     * @AclAncestor("pim_importexport_import_profile_edit")
     */
    public function putImportAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->putAction($request, $identifier);
    }

    /**
     * @AclAncestor("pim_importexport_export_profile_edit")
     */
    public function putExportAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->putAction($request, $identifier);
    }

    /**
     * @AclAncestor("pim_importexport_import_profile_remove")
     */
    public function deleteImportAction(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->deleteAction($code);
    }

    /**
     * @AclAncestor("pim_importexport_export_profile_remove")
     */
    public function deleteExportAction(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->deleteAction($code);
    }

    /**
     * Launch an import job.
     *
     * @param string $code
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
     * Launch an export job.
     *
     * @param string $code
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
     * Get a job profile.
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

        $normalizedJobInstance = $this->normalizeJobInstance($jobInstance);

        if (isset($normalizedJobInstance['configuration']['storage'])) {
            $normalizedJobInstance['configuration']['storage'] = $this->credentialsEncrypterRegistry->obfuscateCredentials($normalizedJobInstance['configuration']['storage']);
        }

        return new JsonResponse($normalizedJobInstance);
    }

    /**
     * Edit a job profile.
     *
     * @param string $identifier
     *
     * @return Response
     */
    protected function putAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $jobInstance = $this->getJobInstance($identifier);
        $previousJobInstanceParameters = $jobInstance->getRawParameters();
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
        if (0 < count($errors)) {
            return new JsonResponse($errors, 400);
        }

        if (isset($data['configuration']['storage'])) {
            $previousStorageData = $previousJobInstanceParameters['storage'];
            $data['configuration']['storage'] = $this->credentialsEncrypterRegistry->encryptCredentials($previousStorageData, $data['configuration']['storage']);
        }
        $this->updater->update($jobInstance, $data);

        try {
            $this->eventDispatcher->dispatch(
                new GenericEvent($jobInstance, ['data' => $data]),
                JobInstanceEvents::PRE_SAVE
            );
        } catch (JobInstanceCannotBeUpdatedException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $this->saver->save($jobInstance);

        $this->eventDispatcher->dispatch(
            new GenericEvent($jobInstance, ['data' => $data]),
            JobInstanceEvents::POST_SAVE
        );

        $normalizedJobInstance = $this->normalizeJobInstance($jobInstance);

        if (isset($normalizedJobInstance['configuration']['storage'])) {
            $normalizedJobInstance['configuration']['storage'] = $this->credentialsEncrypterRegistry->obfuscateCredentials($normalizedJobInstance['configuration']['storage']);
        }

        return new JsonResponse($normalizedJobInstance);
    }

    protected function deleteAction(string $code): Response
    {
        $jobInstance = $this->getJobInstance($code);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.delete')) {
            throw new AccessDeniedHttpException();
        }

        $this->remover->remove($jobInstance);

        return new JsonResponse(null, 204);
    }

    /**
     * @throws AccessDeniedHttpException
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

        if ($file instanceof UploadedFile) {
            if (UPLOAD_ERR_OK !== $file->getError()) {
                return new JsonResponse(['message' => 'pim_import_export.entity.import_profile.flash.upload.error'], 400);
            }

            $violations = $this->validator->validate($file);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = [
                        'message' => $violation->getMessage(),
                        'invalid_value' => $violation->getInvalidValue(),
                    ];
                }

                return new JsonResponse($errors, 400);
            }

            $fileName = $this->getSanitizedClientFileName($file);
            $jobFileLocation = new JobFileLocation($code.DIRECTORY_SEPARATOR.$fileName, true);

            if ($this->filesystem->fileExists($jobFileLocation->path())) {
                $this->filesystem->delete($jobFileLocation->path());
            }

            $fileContent = fopen($file->getPathname(), 'r');
            $this->filesystem->writeStream($jobFileLocation->path(), $fileContent);
            if (is_resource($fileContent)) {
                fclose($fileContent);
            }

            $rawParameters = $jobInstance->getRawParameters();
            $filePath = $jobFileLocation->path();
            $rawParameters['storage'] = [
                'type' => ManualUploadStorage::TYPE,
                'file_path' => $filePath,
            ];

            $jobInstance->setRawParameters($rawParameters);
        }

        $rawParameters = $jobInstance->getRawParameters();
        if (NoneStorage::TYPE === $rawParameters['storage']['type'] && JobInstance::TYPE_IMPORT === $jobInstance->getType()) {
            throw new BadRequestException();
        }

        $validationGroups = null !== $file ? ['Default', 'Execution', 'UploadExecution'] : ['Default', 'Execution'];
        $errors = $this->getValidationErrors($jobInstance, $validationGroups);
        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $jobExecution = $this->launchJob($jobInstance);

        if (!$this->securityFacade->isGranted(sprintf('pim_importexport_%s_execution_show', $jobInstance->getType()))) {
            return new JsonResponse('', 200);
        }

        return new JsonResponse([
            'redirectUrl' => '#'.$this->router->generate(
                'akeneo_job_process_tracker_details',
                ['id' => $jobExecution->getId()]
            ),
        ], 200);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function getJobInstance(string $code): JobInstance
    {
        $jobInstance = $this->repository->findOneByIdentifier($code);
        if (null === $jobInstance) {
            throw new NotFoundHttpException(sprintf('%s entity not found', JobInstance::class));
        }

        if (!$this->jobRegistry->has($jobInstance->getJobName())) {
            $message = sprintf(
                'The following %s does not exist anymore. Please check configuration:<br />Connector: %s<br />Type: %s<br />Alias: %s',
                $jobInstance->getType(),
                $jobInstance->getConnector(),
                $jobInstance->getType(),
                $jobInstance->getJobName()
            );
            throw new NotFoundHttpException($message);
        }

        return $jobInstance;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getJobNamesAction(Request $request): JsonResponse
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

    protected function getValidationErrors(JobInstance $jobInstance, ?array $groups = null): array
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
                $accessor->setValue($errors, '[configuration]'.$error->getPropertyPath(), $error->getMessage());
                $errors['normalized_errors'][] = $this->constraintViolationNormalizer->normalize(
                    $error,
                    'internal_api',
                    [
                        'translate' => false,
                    ]
                );
            }
        }

        $globalViolations = $this->validator->validate($jobInstance, new Valid(), ['Default']);
        if ($globalViolations->count() > 0) {
            foreach ($globalViolations as $error) {
                $errors[$error->getPropertyPath()] = $error->getMessage();
                $errors['normalized_errors'][] = $this->constraintViolationNormalizer->normalize(
                    $error,
                    'internal_api',
                    [
                        'translate' => false,
                    ]
                );
            }
        }

        return $errors;
    }

    protected function normalizeJobInstance(JobInstance $jobInstance): array
    {
        $normalizedJobInstance = $this->jobInstanceNormalizer->normalize($jobInstance, 'standard');

        return array_merge($normalizedJobInstance, [
            'meta' => [
                'form' => $this->formProvider->getForm($jobInstance),
                'id' => $jobInstance->getId(),
            ],
        ]);
    }

    /**
     * Allow to validate and run the job.
     */
    protected function launchJob(JobInstance $jobInstance): JobExecution
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $configuration = $jobInstance->getRawParameters();
        $configuration['send_email'] = true;
        $configuration['users_to_notify'][] = $user->getUserIdentifier();

        return $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }

    /**
     * @AclAncestor("pim_importexport_import_profile_create")
     */
    public function createImportAction(Request $request): Response
    {
        return $this->createAction($request, 'import');
    }

    /**
     * @AclAncestor("pim_importexport_export_profile_create")
     */
    public function createExportAction(Request $request): Response
    {
        return $this->createAction($request, 'export');
    }

    public function duplicateAction(Request $request, $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $jobToDuplicate = $this->getJobInstance($code);
        if (!$this->securityFacade->isGranted(sprintf('pim_importexport_%s_profile_create', $jobToDuplicate->getType()))) {
            throw new AccessDeniedException();
        }

        $duplicatedJobInstance = $this->jobInstanceFactory->createJobInstance($jobToDuplicate->getType());
        $duplicatedJobInstance->setJobName($jobToDuplicate->getJobName());

        $data = json_decode($request->getContent(), true);

        $normalizedJobToDuplicate = $this->normalizeJobInstance($jobToDuplicate);
        $normalizedJobToDuplicate['code'] = $data['code'] ?? '';
        $normalizedJobToDuplicate['label'] = $data['label'] ?? '';
        $this->updater->update($duplicatedJobInstance, $normalizedJobToDuplicate);

        $violations = $this->validator->validate($duplicatedJobInstance);
        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['jobInstance' => $duplicatedJobInstance, 'translate' => false],
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->saver->save($duplicatedJobInstance);

        $this->eventDispatcher->dispatch(
            new GenericEvent($duplicatedJobInstance, ['data' => $this->normalizeJobInstance($jobToDuplicate)]),
            JobInstanceEvents::POST_SAVE,
        );

        return new JsonResponse(['code' => $data['code']]);
    }

    protected function createAction(Request $request, string $type): Response
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
            new GenericEvent($jobInstance, ['data' => $data]),
            JobInstanceEvents::POST_SAVE
        );

        $normalizedJobInstance = $this->normalizeJobInstance($jobInstance);

        return new JsonResponse($normalizedJobInstance);
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

    private function getSanitizedClientFileName(UploadedFile $file): string
    {
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $file->getClientOriginalName());
    }
}
