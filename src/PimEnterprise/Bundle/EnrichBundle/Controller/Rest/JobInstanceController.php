<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController as BaseJobInstanceController;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Override of the CE controller to handle permissions.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class JobInstanceController extends BaseJobInstanceController
{
    /** @var JobProfileAccessManager */
    protected $accessManager;

    /** @var EntityRepository */
    protected $userGroupRepository;

    /**
     * {@inheritdoc}
     *
     * JobProfileAccessManager $accessManager
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
        string $uploadTmpDir,
        JobProfileAccessManager $accessManager,
        EntityRepository $userGroupRepository
    ) {
        parent::__construct(
            $repository,
            $jobRegistry,
            $jobInstanceNormalizer,
            $updater,
            $saver,
            $remover,
            $validator,
            $jobParameterValidator,
            $jobParamsFactory,
            $jobLauncher,
            $tokenStorage,
            $router,
            $formProvider,
            $objectFilter,
            $constraintViolationNormalizer,
            $jobInstanceFactory,
            $uploadTmpDir
        );

        $this->accessManager = $accessManager;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function putAction(Request $request, $identifier): JsonResponse
    {
        $response = parent::putAction($request, $identifier);
        if(!$response->isOk()) {
            return $response;
        }

        $data = json_decode($request->getContent(), true);
        $jobInstance = $this->getJobInstance($data['code']);

        if (isset($data['permissions'])) {
            $this->saveJobInstancePermissions($jobInstance, $data['permissions']);
        }

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * {@inheritdoc}
     */
    protected function createAction(Request $request, string $type): JsonResponse
    {
        $response = parent::createAction($request, $type);
        if(!$response->isOk()) {
            return $response;
        }

        $data = json_decode($request->getContent(), true);
        $jobInstance = $this->getJobInstance($data['code']);

        if (isset($data['permissions'])) {
            $this->saveJobInstancePermissions($jobInstance, $data['permissions']);
        }

        return new JsonResponse($this->normalizeJobInstance($jobInstance));
    }

    /**
     * @param JobInstance $jobInstance
     * @param array       $groupNames
     */
    protected function saveJobInstancePermissions(JobInstance $jobInstance, array $groupNames): void
    {
        $this->accessManager->setAccess(
            $jobInstance,
            $this->getGroups($groupNames['execute']),
            $this->getGroups($groupNames['edit'])
        );
    }

    /**
     * @param string[] $groupNames
     *
     * @return GroupInterface[]
     */
    protected function getGroups($groupNames): iterable
    {
        return array_filter($this->userGroupRepository->findAll(), function ($group) use ($groupNames) {
            return in_array($group->getName(), $groupNames);
        });
    }
}
