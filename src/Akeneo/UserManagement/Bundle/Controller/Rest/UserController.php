<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Event\UserEvent;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException;
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
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * User rest controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserController
{
    private const PASSWORD_MINIMUM_LENGTH = 8;
    private const PASSWORD_MAXIMUM_LENGTH = 4096;

    protected TokenStorageInterface $tokenStorage;
    protected NormalizerInterface $normalizer;
    protected ObjectRepository $repository;
    protected ObjectUpdaterInterface $updater;
    protected ValidatorInterface $validator;
    protected SaverInterface $saver;
    protected NormalizerInterface $constraintViolationNormalizer;
    protected SimpleFactoryInterface $factory;
    protected UserPasswordEncoderInterface $encoder;
    private EventDispatcherInterface $eventDispatcher;
    private Session $session;
    private ObjectManager $objectManager;
    private NumberFactory $numberFactory;
    private RemoverInterface $remover;
    private TranslatorInterface $translator;
    private SecurityFacade $securityFacade;

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
        RemoverInterface $remover,
        NumberFactory $numberFactory,
        TranslatorInterface $translator,
        SecurityFacade $securityFacade
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
        $this->numberFactory = $numberFactory;
        $this->translator = $translator;
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

        $user = $this->normalizer->normalize($user, 'internal_api');
        $decimalSeparator = $this->additionalProperties($user);
        $result = array_merge($decimalSeparator, $user);

        return new JsonResponse($result);
    }

    /**
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction(int $identifier): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        $currentUserIdentifier = null !== $token ? $token->getUser()->getId() : null;

        if (
            $currentUserIdentifier !== $identifier &&
            !$this->securityFacade->isGranted('pim_user_user_index')
        ) {
            throw new AccessDeniedHttpException();
        }

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

        if (!$this->securityFacade->isGranted('pim_user_role_edit')) {
            unset($data['roles']);
        }
        if (!$this->securityFacade->isGranted('pim_user_group_edit')) {
            unset($data['groups']);
        }

        return $this->updateUser($user, $data);
    }

    /**
     * @return JsonResponse|RedirectResponse
     * @throws \HttpException
     *
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
        if (null === $currentUser || $currentUser->getId() !== $user->getId()) {
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
     * @AclAncestor("pim_user_user_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->factory->create();
        $content = json_decode($request->getContent(), true);

        $violations = new ConstraintViolationList();
        $passwordViolations = $this->validatePasswordCreate($content);

        unset($content['password_repeat']);

        if (0 === $passwordViolations->count()) {
            $this->updater->update($user, $content);
            $violations = $this->validator->validate($user);
        }

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
     * @AclAncestor("pim_user_user_create")
     */
    public function duplicateAction(Request $request, int $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $baseUser = $this->getUserOr404($identifier);
        $targetUser = $baseUser->duplicate();
        $content = \json_decode($request->getContent(), true);

        $violations = new ConstraintViolationList();
        $passwordViolations = $this->validatePasswordCreate($content);

        unset($content['password_repeat']);

        if (0 === $passwordViolations->count()) {
            $this->updater->update($targetUser, $content);
            $violations = $this->validator->validate($targetUser);
        }

        if ($violations->count() > 0 || $passwordViolations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $targetUser]
                );
            }
            foreach ($passwordViolations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $targetUser]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($targetUser);

        return new JsonResponse($this->normalizer->normalize($targetUser, 'internal_api'));
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

        try {
            $this->remover->remove($user);
        } catch (UserCannotBeDeletedException $e) {
            return new JsonResponse(['message' => $this->translator->trans($e->getMessage())], 400);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function getUserOr404($identifier): UserInterface
    {
        $user = $this->repository->findOneBy(['id' => $identifier]);

        if (null === $user || true === $user->isApiUser()) {
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
        $violations = new ConstraintViolationList();
        $passwordViolations = new ConstraintViolationList();

        $previousUserName = $user->getUsername();
        if ($this->isPasswordUpdating($data)) {
            $passwordViolations = $this->validatePassword($user, $data);
            if ($passwordViolations->count() === 0) {
                $data['password'] = $data['new_password'];
            }
        }

        unset($data['current_password'], $data['new_password'], $data['new_password_repeat']);

        if (0 === $passwordViolations->count()) {
            $this->updater->update($user, $data);
            $violations = $this->validator->validate($user);
        }

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
            $this->objectManager->refresh($user);

            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($this->update($user, $previousUserName), 'internal_api'));
    }

    private function validatePasswordCreate(array $data): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        $newPassword = $data['password'] ?? '';
        $newPasswordRepeat = $data['password_repeat'] ?? '';

        $violations->addAll($this->validatePasswordLength($newPassword, 'password'));
        $violations->addAll($this->validatePasswordMatch($newPassword, $newPasswordRepeat, 'password_repeat'));

        return $violations;
    }

    private function validatePassword(UserInterface $user, array $data): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $newPasswordRepeat = $data['new_password_repeat'] ?? '';

        if (!$this->encoder->isPasswordValid($user, $currentPassword)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.current_password.wrong'),
                '',
                [],
                '',
                'current_password',
                ''
            ));
        }

        $violations->addAll($this->validatePasswordLength($newPassword, 'new_password'));
        $violations->addAll($this->validatePasswordMatch($newPassword, $newPasswordRepeat, 'new_password_repeat'));

        return $violations;
    }

    private function validatePasswordMatch(string $password, string $passwordRepeat, string $propertyPath): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if ($password !== $passwordRepeat) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password_repeat.not_match'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
        }

        return $violations;
    }

    private function validatePasswordLength(string $password, string $propertyPath): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if (self::PASSWORD_MINIMUM_LENGTH > mb_strlen($password)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password.minimum_length'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
            // We have to use `strlen` here because Symfony's BasePasswordEncoder will check
            // the actual character count when trying to encode it with salt.
            // See: Symfony\Component\Security\Core\Encoder\BasePasswordEncoder
        } elseif (self::PASSWORD_MAXIMUM_LENGTH < strlen($password)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password.maximum_length'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
        }

        return $violations;
    }

    private function isPasswordUpdating($data): bool
    {
        return (isset($data['current_password']) && !empty($data['current_password'])) ||
            (isset($data['new_password']) && !empty($data['new_password'])) ||
            (isset($data['new_password_repeat']) && !empty($data['new_password_repeat']));
    }

    private function additionalProperties($user): array
    {
        $decimalSeparator['ui_locale_decimal_separator'] = $this->numberFactory
            ->create(['locale' => $user['user_default_locale']])
            ->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return $decimalSeparator;
    }
}
