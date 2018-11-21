<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /**
     * @param RoleRepository      $roleRepository
     * @param NormalizerInterface $normalizer
     * @param UserContext         $userContext
     */
    public function __construct(
        RoleRepository $roleRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext
    ) {
        $this->roleRepository = $roleRepository;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $queryBuildder = $this->roleRepository->getAllButAnonymousQB();
        $roles = $queryBuildder->getQuery()->execute();

        return new JsonResponse($this->normalizer->normalize(
            $roles,
            'internal_api',
            $this->userContext->toArray()
        ));
    }
}
