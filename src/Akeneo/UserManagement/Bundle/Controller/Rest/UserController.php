<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * User rest controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserController
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $repository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param TokenStorageInterface                 $tokenStorage
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param SaverInterface                        $saver
     * @param NormalizerInterface                   $constraintViolationNormalizer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->repository = $repository;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
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
    public function getAction($identifier): JsonResponse
    {
        $user = $this->repository->findOneByIdentifier($identifier);

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * TODO Change ACL Ancestor
     * @AclAncestor("pim_enrich_grouptype_edit")
     */
    public function postAction(Request $request, $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->getUserOr404($identifier);
        $data = json_decode($request->getContent(), true);
        unset($data['code']);
        unset($data['last_login']);
        unset($data['login_count']);
        $this->updater->update($user, $data);

        $violations = $this->validator->validate($user);
        if (0 < $violations->count()) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }

            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    private function getUserOr404($username): ?UserInterface
    {
        $user = $this->repository->findOneByIdentifier($username);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('Username with code "%s" not found', $username)
            );
        }

        return $user;
    }
}
