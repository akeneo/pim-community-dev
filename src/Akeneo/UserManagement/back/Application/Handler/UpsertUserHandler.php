<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Updater\UserUpdater;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use Akeneo\UserManagement\ServiceApi\ViolationsException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpsertUserHandler implements UpsertUserHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserUpdater $userUpdater,
        private UserFactory $userFactory,
        private SaverInterface $userSaver,
        private ValidatorInterface $validator,
    ) {
    }

    public function handle(UpsertUserCommand $upsertUserCommand): void
    {
        /** @var UserInterface|null $user */
        $user = $this->userRepository->findOneBy(['username' => $upsertUserCommand->username]);

        if (null === $user) {
            $user = $this->userFactory->create();
            $user->setUsername($upsertUserCommand->username);
            $this->defineType($user, $upsertUserCommand->type);
        }

        try {
            if ($user->getType() !== $upsertUserCommand->type) {
                throw new InvalidPropertyException(
                    'type',
                    $upsertUserCommand->type,
                    null,
                    'You cannot change the user type'
                );
            }

            $userData = [
                'password' => $upsertUserCommand->password,
                'first_name' => $upsertUserCommand->firstName,
                'last_name' => $upsertUserCommand->lastName,
                'email' => $upsertUserCommand->email,
                'group_ids' => $upsertUserCommand->groupIds,
                'roles' => $upsertUserCommand->roleCodes,
            ];

            $this->userUpdater->update($user, $userData);
        } catch (InvalidPropertyException $exception) {
            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    $exception->getMessage(),
                    $exception->getMessage(),
                    [],
                    $upsertUserCommand,
                    $exception->getPropertyName(),
                    $exception->getPropertyValue()
                ),
            ]);

            throw new ViolationsException($violations);
        }

        $constraintViolations = $this->validator->validate($user);

        if (0 < $constraintViolations->count()) {
            throw new ViolationsException($constraintViolations);
        }

        $this->userSaver->save($user);
    }

    private function defineType(UserInterface $user, string $type): void
    {
        switch ($type) {
            case User::TYPE_USER:
                return;
            case User::TYPE_API:
                $user->defineAsApiUser();

                return;
            case User::TYPE_JOB:
                $user->defineAsJobUser();

                return;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid user type "%s"', $type));
        }
    }
}
