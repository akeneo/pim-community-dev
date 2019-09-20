<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
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

    /** @var RemoverInterface */
    private $remover;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @todo merge 3.2:
     *       - remove the $objectManager argument
     *       - the last two arguments ($translator $remover) must not be nullable anymore
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
        ObjectManager $objectManager,
        ?RemoverInterface $remover = null,
        ?TranslatorInterface $translator = null
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
        $this->remover = $remover;
        $this->translator = $translator;
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
     * @param int     $identifier
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

        return $this->updateUser($user, $data);
    }

    /**
     * @param Request $request
     * @param int     $identifier
     *
     * @throws \HttpException
     *
     * @return JsonResponse|RedirectResponse
     */
    public function updateProfileAction(Request $request, int $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->getUserOr404($identifier);
        $data = json_decode($request->getContent(), true);

        $token = $this->tokenStorage->getToken();
        $currentUser = null !== $token ? $token->getUser() : null;
        if (null === $currentUser || $user->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException();
        }

        unset($data['code']);
        unset($data['roles']);
        unset($data['groups']);

        return $this->updateUser($user, $data);
    }

    protected function update(UserInterface $user, ?string $previousUsername = null)
    {
        $this->eventDispatcher->dispatch(
            UserEvent::POST_UPDATE,
            new GenericEvent($user, [
                'current_user' => $this->tokenStorage->getToken()->getUser(),
                'previous_username' => $previousUsername,
            ])
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
     * @param int     $identifier
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

        // todo merge 3.2: remove the condition, and the whole "else" body
        if (null !== $this->remover) {
            $this->remover->remove($user);
        } else {
            $this->objectManager->remove($user);
            $this->objectManager->flush();
        }

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
     * @param UserInterface $user
     * @param array $data
     *
     * @return JsonResponse
     */
    private function updateUser(UserInterface $user, array $data): JsonResponse
    {
        $previousUserName = $data['username'];
        if ($this->isPasswordUpdating($data)) {
            $passwordViolations = $this->validatePassword($user, $data);
            if ($passwordViolations->count() === 0) {
                $data['password'] = $data['new_password'];
            }
        }

        unset($data['current_password'], $data['new_password'], $data['new_password_repeat']);

        $this->updater->update($user, $data);

        $violations = $this->validator->validate($user);
        if (0 < $violations->count() || (isset($passwordViolations) && 0 < $passwordViolations->count())) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }
            if (isset($passwordViolations)) {
                unset($data['password']);
                foreach ($passwordViolations as $violation) {
                    $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                        $violation,
                        'internal_api'
                    );
                }
            }

            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($this->update($user, $previousUserName), 'internal_api'));
    }

    /**
     * Validate username only at creation.
     *
     * @param array $data
     *
     * @return ConstraintViolationListInterface
     *
     * @see https://akeneo.atlassian.net/browse/PIM-8777
     */
    private function validateUsernameCreate(array $data): ConstraintViolationListInterface
    {
        $violations = [];

        if (!isset($data['username'])) {
            return new ConstraintViolationList([]);
        }

        if (strstr($data['username'], ' ') !== false) {
            $violations[] = new ConstraintViolation('Username should not contain space character', '', [], '', 'username', '');
        }

        return new ConstraintViolationList($violations);
    }

    /**
     * @todo merge 3.2:
     *       - remove check null for translator
     */
    private function validatePassword(UserInterface $user, $data): ConstraintViolationListInterface
    {
        $violations = [];
        if (
            isset($data['current_password']) &&
            '' !== $data['current_password'] &&
            !$this->encoder->isPasswordValid($user, $data['current_password']) ||
            (isset($data['current_password']) && '' === $data['current_password']) ||
            !isset($data['current_password'])
        ) {
            $violations[] = new ConstraintViolation(
                $this->translator ?
                    $this->translator->trans(
                        'pim_user.user.fields_errors.current_password.wrong'
                    ) : 'Wrong password',
                '',
                [],
                '',
                'current_password',
                ''
            );
        }
        if (
            isset($data['new_password']) &&
            isset($data['new_password_repeat']) &&
            '' !== $data['new_password'] &&
            '' !== $data['new_password_repeat'] &&
            $data['new_password'] !== $data['new_password_repeat']
        ) {
            $violations[] = new ConstraintViolation(
                $this->translator ?
                    $this->translator->trans(
                        'pim_user.user.fields_errors.new_password_repeat.not_match'
                    ) : 'Password does not match', '', [], '', 'new_password_repeat', ''
            );
        }
        if (
            isset($data['new_password']) && strlen($data['new_password']) < 2
        ) {
            $violations[] = new ConstraintViolation(
                $this->translator ?
                    $this->translator->trans(
                        'pim_user.user.fields_errors.new_password.minimum_length'
                    ) : 'Password must contains at least 2 characters', '', [], '', 'new_password', ''
            );
        }

        return new ConstraintViolationList($violations);
    }

    private function isPasswordUpdating($data): bool
    {
        return
            (isset($data['current_password']) && !empty($data['current_password'])) ||
            (isset($data['new_password']) && !empty($data['new_password'])) ||
            (isset($data['new_password_repeat']) && !empty($data['new_password_repeat']));
    }
}
