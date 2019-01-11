<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Event\UserEvent;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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

    /** @var ObjectRepository */
    protected $repository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var Session */
    private $session;

    /** @var ObjectManager */
    private $objectManager;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param NormalizerInterface $normalizer
     * @param ObjectRepository $repository
     * @param ObjectUpdaterInterface $updater
     * @param ValidatorInterface $validator
     * @param SaverInterface $saver
     * @param NormalizerInterface $constraintViolationNormalizer
     * @param SimpleFactoryInterface $factory
     * @param UserPasswordEncoderInterface $encoder
     * @param EventDispatcherInterface $eventDispatcher
     * @param Session $session
     * @param ObjectManager $objectManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NormalizerInterface $normalizer,
        ObjectRepository $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        NormalizerInterface $constraintViolationNormalizer,
        SimpleFactoryInterface $factory,
        UserPasswordEncoderInterface $encoder,
        EventDispatcherInterface $eventDispatcher,
        Session $session,
        ObjectManager $objectManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->normalizer = $normalizer;
        $this->repository = $repository;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->factory = $factory;
        $this->encoder = $encoder;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->objectManager = $objectManager;
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
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction(int $identifier): JsonResponse
    {
        $user = $this->getUserOr404($identifier);

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    /**
     * @param Request $request
     * @param int $identifier
     *
     * @return Response
     *
     * @AclAncestor("pim_user_user_edit")
     */
    public function postAction(Request $request, $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->getUserOr404($identifier);
        $data = json_decode($request->getContent(), true);

        //code is useful to reach the route, cannot forget it in the query
        unset($data['code']);
        $previousUserName = $data['username'];
        $passwordViolations = $this->validatePassword($user, $data);
        if ($this->isPasswordUpdating($data) && $passwordViolations->count() === 0) {
            $data['password'] = $data['new_password'];
        }
        unset($data['current_password'], $data['new_password'], $data['new_password_repeat']);

        $this->updater->update($user, $data);

        $violations = $this->validator->validate($user);
        if (0 < $violations->count() || 0 < $passwordViolations->count()) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }
            foreach ($passwordViolations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }

            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($this->update($user, $previousUserName), 'internal_api'));
    }

    protected function update(UserInterface $user, ?string $previousUsername = null)
    {
        $this->eventDispatcher->dispatch(
            UserEvent::POST_UPDATE,
            new GenericEvent($user, ['current_user' => $this->tokenStorage->getToken()->getUser(), 'previous_username' => $previousUsername])
        );

        $this->session->remove('dataLocale');
        return $user;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->factory->create();
        $content = json_decode($request->getContent(), true);

        $passwordViolations = $this->validatePasswordCreate($content);
        unset($content['password_repeat']);

        $this->updater->update($user, $content);

        $violations = $this->validator->validate($user);

        if ($violations->count() > 0 || $passwordViolations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $user]
                );
            }
            foreach ($passwordViolations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $user]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    /**
     * @param Request $request
     * @param int  $identifier
     *
     * @return Response
     */
    public function deleteAction(Request $request, int $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->getUserOr404($identifier);

        $token = $this->tokenStorage->getToken();
        $currentUser = null !== $token ? $token->getUser() : null;
        if ($currentUser !== null && $user->getId() === $currentUser->getId()) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function getUserOr404($identifier): UserInterface
    {
        $user = $this->repository->findOneBy(['id' => $identifier]);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('Username with id "%s" not found', $identifier)
            );
        }

        return $user;
    }

    /**
     * @param array $data
     *
     * @return ConstraintViolationListInterface
     */
    private function validatePasswordCreate(array $data): ConstraintViolationListInterface
    {
        $violations = [];

        if (!isset($data['password'])) {
            return new ConstraintViolationList([]);
        }

        if (($data['password_repeat'] ?? '') !== $data['password']) {
            $violations[] = new ConstraintViolation('Passwords do not match', '', [], '', 'password_repeat', '');
        }

        return new ConstraintViolationList($violations);
    }

    private function validatePassword(UserInterface $user, $data): ConstraintViolationListInterface
    {
        $violations = [];
        if (
            isset($data['current_password']) &&
            '' !== $data['current_password'] &&
            !$this->encoder->isPasswordValid($user, $data['current_password'])
        ) {
            $violations[] = new ConstraintViolation('Wrong password', '', [], '', 'current_password', '');
        }
        if (
            isset($data['new_password']) &&
            isset($data['new_password_repeat']) &&
            '' !== $data['new_password'] &&
            '' !== $data['new_password_repeat'] &&
            $data['new_password'] !== $data['new_password_repeat']
        ) {
            $violations[] = new ConstraintViolation('Password does not match', '', [], '', 'new_password_repeat', '');
        }

        return new ConstraintViolationList($violations);
    }

    private function isPasswordUpdating($data): bool
    {
        return
            isset($data['current_password']) &&
            isset($data['new_password']) &&
            isset($data['new_password_repeat']) &&
            '' !== $data['current_password'] &&
            '' !== $data['new_password'] &&
            '' !== $data['new_password_repeat'];
    }
}
