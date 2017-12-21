<?php

namespace Pim\Bundle\UserBundle\Controller;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param TokenStorageInterface                 $tokenStorage
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $userRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $userRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->userRepository = $userRepository;
    }

    /**
     * Get the user collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = $request->query->get('options', ['limit' => 20]);

        $users = $this->userRepository->findBySearch(
            $request->query->get('search'),
            $options
        );

        $normalized = [];
        foreach ($users as $user) {
            $normalized[$user->getUsername()] = $this->normalizer->normalize($user, 'internal_api');
            $normalized[$user->getUsername()]['code'] = $user->getUsername();
            $normalized[$user->getUsername()]['labels']['en_US'] = $user->getUsername();
        }

        return new JsonResponse($normalized);
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

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }
}
