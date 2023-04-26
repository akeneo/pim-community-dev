<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\ServiceApi\PasswordCheckerInterface;
use Akeneo\UserManagement\ServiceApi\User\UpdateUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpdateUserHandlerInterface;
use Akeneo\UserManagement\ServiceApi\ViolationsException;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateUserHandler implements UpdateUserHandlerInterface
{
    public function __construct(
        private readonly ObjectUpdaterInterface $updater,
        private readonly ValidatorInterface $validator,
        private readonly ObjectManager $objectManager,
        private readonly SaverInterface $saver,
        private readonly PasswordCheckerInterface $passwordChecker,
    ) {
    }

    /**
     * @throws ViolationsException
     */
    public function handle(UpdateUserCommand $updateUserCommand): UserInterface
    {
        $violations = new ConstraintViolationList();
        $passwordViolations = new ConstraintViolationList();

        $user = $updateUserCommand->user;
        $data = $updateUserCommand->data;
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
            unset($data['password']);
            $this->objectManager->refresh($user);
            $allViolations = new ConstraintViolationList($violations);
            $allViolations->addAll($passwordViolations);
            throw new ViolationsException($allViolations);
        }

        $this->saver->save($user);
        return $user;
    }

    private function isPasswordUpdating($data): bool
    {
        return array_key_exists('current_password', $data) || array_key_exists('new_password', $data) || array_key_exists('new_password_repeat', $data);
    }
}
