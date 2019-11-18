<?php

namespace Pim\Bundle\UserBundle\Controller;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * User rest controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRestController
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $userRepository;

    /** @var SecurityFacade|null */
    private $securityFacade;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param NormalizerInterface $normalizer
     * @param IdentifiableObjectRepositoryInterface $userRepository
     * @param SecurityFacade|null $securityFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $userRepository,
        SecurityFacade $securityFacade = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->userRepository = $userRepository;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @return JsonResponse
     */
    public function getCurrentAction()
    {
        $token = $this->tokenStorage->getToken();
        $user = null !== $token ? $token->getUser() : null;

        if (null === $user) {
            throw new NotFoundHttpException('No logged in user found');
        }

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    /**
     * @param $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $user = $this->userRepository->findOneByIdentifier($identifier);

        $token = $this->tokenStorage->getToken();
        $currentUserId = null !== $token ? $token->getUser()->getId() : null;

        // To not report in 3.x (it's already fixed)
        if (null !== $this->securityFacade) {
            if ($currentUserId !== $user->getId() &&
                !$this->securityFacade->isGranted('pim_user_user_index')) {
                throw new AccessDeniedHttpException();
            }
        }

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }
}
