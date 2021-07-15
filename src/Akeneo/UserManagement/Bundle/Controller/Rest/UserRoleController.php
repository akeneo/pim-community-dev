<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Processor\Normalization\Processor;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Role controller
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleController
{
    /** @var RoleRepository */
    protected $roleRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var UserContext */
    protected $userContext;

    /** @var BulkSaverInterface */
    private BulkSaverInterface $saver;

    /**  @var SimpleFactoryInterface */
    private SimpleFactoryInterface $factory;

    /** @var ObjectUpdaterInterface */
    private ObjectUpdaterInterface $updater;

    /** @var ValidatorInterface */
    private ValidatorInterface $validator;

    /** @var NormalizerInterface */
    private NormalizerInterface $constraintViolationNormalizer;

    /**  @var NormalizerInterface */
    private NormalizerInterface $basicNormalizer;

    /** @var ArrayConverterInterface */
    private ArrayConverterInterface $standardToFlatArrayConverter;

    /** @var ArrayConverterInterface */
    private ArrayConverterInterface $flatToStandardArrayConverter;

    /**
     * @param RoleRepository $roleRepository
     * @param Processor $normalizer
     * @param ArrayConverterInterface $standardToFlatArrayConverter
     * @param ArrayConverterInterface $flatToStandardArrayConverter
     * @param NormalizerInterface $basicNormalizer
     * @param UserContext $userContext
     * @param SimpleFactoryInterface $factory
     * @param ObjectUpdaterInterface $updater
     * @param BulkSaverInterface $saver
     * @param ValidatorInterface $validator
     * @param NormalizerInterface $constraintViolationNormalizer
     */
    public function __construct(
        RoleRepository $roleRepository,
        Processor $normalizer,
        ArrayConverterInterface $standardToFlatArrayConverter,
        ArrayConverterInterface $flatToStandardArrayConverter,
        NormalizerInterface $basicNormalizer,
        UserContext $userContext,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer

    ) {
        $this->roleRepository = $roleRepository;
        $this->normalizer = $normalizer;
        $this->standardToFlatArrayConverter = $standardToFlatArrayConverter;
        $this->flatToStandardArrayConverter = $flatToStandardArrayConverter;
        $this->basicNormalizer = $basicNormalizer;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @AclAncestor("pim_user_role_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $userRole = $this->factory->create();
        $content = json_decode($request->getContent(), true);

        $datas = $this->flatToStandardArrayConverter->convert($content);

        $this->updater->update($userRole, $datas);
        $violations = $this->validator->validate($userRole);

        if ($violations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['userRole' => $userRole]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->saveAll([$userRole]);

        $userRole = $this->roleRepository->findOneByIdentifier($userRole->role()->getRole());

        return new JsonResponse($this->standardToFlatArrayConverter->convert($this->normalizer->process($userRole)));
    }

    /**
     * @param int $identifier
     * @AclAncestor("pim_user_role_edit")
     * @return JsonResponse
     */
    public function getAction(int $identifier): JsonResponse
    {
        $userRole = $this->getUserRoleOr404($identifier);

        return new JsonResponse($this->standardToFlatArrayConverter->convert($this->normalizer->process($userRole)));
    }


    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $queryBuildder = $this->roleRepository->getAllButAnonymousQB();
        $roles = $queryBuildder->getQuery()->execute();

        return new JsonResponse($this->basicNormalizer->normalize(
            $roles,
            'internal_api',
            $this->userContext->toArray()
        ));
    }

    /**
     * @param $identifier
     * @return Role
     */
    private function getUserRoleOr404($identifier):Role
    {
        $userRole = $this->roleRepository->findOneBy(['id' => $identifier]);

        if (null === $userRole) {
            throw new NotFoundHttpException(
                sprintf('User Role with id "%s" not found', $identifier)
            );
        }

        return $userRole;
    }

}
