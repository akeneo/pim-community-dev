<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Application\CheckAdminRolePermissions;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommand;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommandHandler;
use Akeneo\UserManagement\Application\Exception\UserNotFoundException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Domain\PasswordCheckerInterface;
use Akeneo\UserManagement\ServiceApi\ViolationsException;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
final class UserController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly NormalizerInterface $normalizer,
        private readonly ObjectRepository $repository,
        private readonly ObjectUpdaterInterface $updater,
        private readonly ValidatorInterface $validator,
        private readonly SaverInterface $saver,
        private readonly NormalizerInterface $constraintViolationNormalizer,
        private readonly SimpleFactoryInterface $factory,
        private readonly RemoverInterface $remover,
        private readonly NumberFactory $numberFactory,
        private readonly TranslatorInterface $translator,
        private readonly SecurityFacade $securityFacade,
        private readonly PasswordCheckerInterface $passwordChecker,
        private readonly UpdateUserCommandHandler $updateUserCommandHandler,
        private readonly CheckAdminRolePermissions $checkAdminRolePermissions,
    ) {
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
     * @throws \HttpException
     *
     * @AclAncestor("pim_user_user_edit")
     */
    public function postAction(Request $request, int $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);

        if (!$this->securityFacade->isGranted('pim_user_role_edit')) {
            unset($data['roles']);
        }
        if (!$this->securityFacade->isGranted('pim_user_group_edit')) {
            unset($data['groups']);
        }

        $adminRolesPrivileges = $this->checkAdminRolePermissions->getRolesWithMinimumAdminPrivileges();
        $adminRolesNamePrivileges = array_map(fn ($role) => $role->getRole(), $adminRolesPrivileges);
        $adminRoleLeft = array_filter($data['roles'], (function ($role) use ($adminRolesNamePrivileges) {
            return in_array($role, $adminRolesNamePrivileges);
        }));
        if(count($adminRoleLeft) < 1) {
            $usersWithAdminRoles = $this->checkAdminRolePermissions->getUsersWithAdminRoles();
            if(count($usersWithAdminRoles) <= 1) {
                $lastUser = $usersWithAdminRoles[0];
                if($lastUser && $lastUser === $user) {
                    throw new NotFoundHttpException('This user is the last with admin privileges');
                }
            }
        }

        return $this->updateUser($identifier, $data);
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

        $data = json_decode($request->getContent(), true);

        $token = $this->tokenStorage->getToken();
        $currentUser = null !== $token ? $token->getUser() : null;
        if (null === $currentUser || $currentUser->getId() !== $identifier) {
            throw new AccessDeniedHttpException();
        }

        unset($data['roles']);
        unset($data['groups']);

        return $this->updateUser($identifier, $data);
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
     * @param array $data
     */
    private function updateUser(int $identifier, array $data): Response
    {
        try {
            $updateUserCommand = new UpdateUserCommand($identifier, $data);
            $user = $this->updateUserCommandHandler->handle($updateUserCommand);

            return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
        } catch (ViolationsException $violationsException) {
            $normalizedViolations = [];
            foreach ($violationsException->violations() as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api'
                );
            }
            return new JsonResponse($normalizedViolations, Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $userNotFoundException) {
            throw new NotFoundHttpException($userNotFoundException->getMessage());
        }
    }

    private function validatePasswordCreate(array $data): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        $newPassword = $data['password'] ?? '';
        $newPasswordRepeat = $data['password_repeat'] ?? '';

        $violations->addAll($this->passwordChecker->validatePasswordLength($newPassword, 'password'));
        $violations->addAll($this->passwordChecker->validatePasswordMatch($newPassword, $newPasswordRepeat, 'password_repeat'));

        return $violations;
    }

    private function additionalProperties($user): array
    {
        $decimalSeparator['ui_locale_decimal_separator'] = $this->numberFactory
            ->create(['locale' => $user['user_default_locale']])
            ->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        return $decimalSeparator;
    }
}
