<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Application\Command\UpdateUserCommand;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Application\Exception\UserNotFoundException;
use Akeneo\UserManagement\Component\Event\UserEvent;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Domain\PasswordCheckerInterface;
use Akeneo\UserManagement\ServiceApi\ViolationsException;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateUserCommandHandler
{
    public function __construct(
        private readonly ObjectUpdaterInterface $updater,
        private readonly ValidatorInterface $validator,
        private readonly ObjectManager $objectManager,
        private readonly SaverInterface $saver,
        private readonly PasswordCheckerInterface $passwordChecker,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Session $session,
        private readonly ObjectRepository $repository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @throws ViolationsException
     */
    public function handle(UpdateUserCommand $updateUserCommand): UserInterface
    {
        $identifier = $updateUserCommand->identifier;
        /** @var UserInterface $user */
        $user = $this->repository->findOneBy(['id' => $identifier]);

        if (null === $user || false === $user->isUiUser()) {
            throw new UserNotFoundException($identifier);
        }

        $violations = new ConstraintViolationList();
        $passwordViolations = new ConstraintViolationList();

        $data = $updateUserCommand->data;

        $previousUserName = $user->getUserIdentifier();

        unset($data['password']);
        if ($this->isPasswordUpdating($data)) {
            $passwordViolations = $this->passwordChecker->validatePassword($user, $data);
            if ($passwordViolations->count() === 0) {
                $data['password'] = $data['new_password'];
            }
        }

        unset($data['current_password'], $data['new_password'], $data['new_password_repeat']);

        if (0 === $passwordViolations->count()) {
            $this->updater->update($user, $data);
            $violations = $this->validator->validate($user);
        }

        if (0 < $violations->count() || 0 < $passwordViolations->count()) {
            $this->objectManager->refresh($user);
            $allViolations = new ConstraintViolationList($violations);
            $allViolations->addAll($passwordViolations);
            throw new ViolationsException($allViolations);
        }

        $this->saver->save($user);

        $this->eventDispatcher->dispatch(
            new GenericEvent($user, [
                'current_user' => $this->tokenStorage->getToken()->getUser(),
                'previous_username' => $previousUserName,
            ]),
            UserEvent::POST_UPDATE
        );

        $this->session->remove('dataLocale');


        return $user;
    }

    private function isPasswordUpdating($data): bool
    {
        return (isset($data['current_password']) && !empty($data['current_password'])) ||
            (isset($data['new_password']) && !empty($data['new_password'])) ||
            (isset($data['new_password_repeat']) && !empty($data['new_password_repeat']));
    }
}
