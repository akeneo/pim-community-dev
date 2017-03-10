<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

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
    protected $simpleJobLauncher;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var RouterInterface */
    protected $router;

    /** @var FormProviderInterface */
    protected $formProvider;

    /** @var ObjectFilterInterface */
    protected $objectFilter;

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
     * @param JobLauncherInterface                  $simpleJobLauncher
     * @param TokenStorageInterface                 $tokenStorage
     * @param RouterInterface                       $router
     * @param FormProviderInterface                 $formProvider
     * @param ObjectFilterInterface                 $objectFilter
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
        JobLauncherInterface $simpleJobLauncher,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        FormProviderInterface $formProvider,
        ObjectFilterInterface $objectFilter
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
        $this->simpleJobLauncher     = $simpleJobLauncher;
        $this->tokenStorage          = $tokenStorage;
        $this->router                = $router;
        $this->formProvider          = $formProvider;
        $this->objectFilter          = $objectFilter;
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
     * @return JsonResponse
     */
    public function putImportAction(Request $request, $identifier)
    {
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
     * @return JsonResponse
     */
    public function putExportAction(Request $request, $identifier)
    {
        return $this->putAction($request, $identifier);
    }

    /**
     * Delete an export job profile
     *
     * @param string $code
     *
     * @AclAncestor("pim_importexport_import_profile_remove")
     *
     * @return JsonResponse
     */
    public function deleteImportAction($code)
    {
        return $this->deleteAction($code);
    }

    /**
     * Delete an export job profile
     *
     * @param string $code
     *
     * @AclAncestor("pim_importexport_export_profile_remove")
     *
     * @return JsonResponse
     */
    public function deleteExportAction($code)
    {
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
     * @return JsonResponse
     */
    public function launchImportAction(Request $request, $code)
    {
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
     * @return JsonResponse
     */
    public function launchExportAction(Request $request, $code)
    {
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
     * @return JsonResponse
     */
    protected function putAction(Request $request, $identifier)
    {
        $jobInstance = $this->getJobInstance($identifier);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.edit')) {
            throw new AccessDeniedHttpException();
        }

        $data = json_decode($request->getContent(), true);
        $this->updater->update($jobInstance, $data);

        $errors = $this->getValidationErrors($jobInstance);
        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $this->saver->save($jobInstance);

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * Delete a job profile
     *
     * @param string $code
     *
     * @return JsonResponse
     */
    protected function deleteAction($code)
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
     * @return JsonResponse
     */
    protected function launchAction(Request $request, $code)
    {
        $jobInstance = $this->getJobInstance($code);
        if ($this->objectFilter->filterObject($jobInstance, 'pim.internal_api.job_instance.execute')) {
            throw new AccessDeniedHttpException();
        }

        $file = $request->files->get('file');
        if ($file) {
            $violations = $this->validator->validate($file);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = [
                        'message'       => $violation->getMessage(),
                        'invalid_value' => $violation->getInvalidValue()
                    ];
                }

                return new JsonResponse($errors, 400);
            }

            $file = $file->move(sys_get_temp_dir(), $file->getClientOriginalName());
            $rawParameters = $jobInstance->getRawParameters();
            $rawParameters['filePath'] = $file->getRealPath();
            $jobInstance->setRawParameters($rawParameters);
        }

        $errors = $this->getValidationErrors($jobInstance);
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
     * @param bool   $checkStatus
     *
     * @throws NotFoundHttpException
     *
     * @return JobInstance
     */
    protected function getJobInstance($code, $checkStatus = true)
    {
        $jobInstance = $this->repository->findOneByIdentifier($code);
        if (null === $jobInstance) {
            throw new NotFoundHttpException(sprintf('%s entity not found', 'Akeneo\Component\Batch\Model\JobInstance'));
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
     * Aggregate validation errors
     *
     * @param JobInstance $jobInstance
     *
     * @return array
     */
    protected function getValidationErrors(JobInstance $jobInstance)
    {
        $rawParameters = $jobInstance->getRawParameters();
        if (!empty($rawParameters)) {
            $job = $this->jobRegistry->get($jobInstance->getJobName());
            $parameters = $this->jobParamsFactory->create($job, $rawParameters);
            $parametersViolations = $this->jobParameterValidator->validate($job, $parameters);
        }

        $errors = [];
        $accessor = PropertyAccess::createPropertyAccessorBuilder()->getPropertyAccessor();
        if ($parametersViolations->count() > 0) {
            foreach ($parametersViolations as $error) {
                $accessor->setValue($errors, '[configuration]' . $error->getPropertyPath(), $error->getMessage());
            }
        }

        $globalViolations = $this->validator->validate($jobInstance, ['Default']);
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
     * @return JobInstance
     */
    protected function launchJob(JobInstance $jobInstance)
    {
        $configuration = $jobInstance->getRawParameters();
        $configuration['send_email'] = true;

        return $this->simpleJobLauncher
            ->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), $configuration);
    }
}
