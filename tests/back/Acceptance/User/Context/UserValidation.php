<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User\Context;

use Akeneo\Test\Acceptance\User\InMemoryGroupRepository;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Updater\UserUpdater;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Use this context to check user validation rules.
 * Create a user with specific values, valid the user object and check errors.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserValidation implements Context
{
    /** @var UserInterface */
    private $user;

    /** @var ValidatorInterface */
    private $userValidator;

    /** @var UserFactory */
    private $userFactory;

    /** @var UserUpdater */
    private $userUpdater;
    /** @var InMemoryGroupRepository */
    private $groupRepository;

    public function __construct(
        UserFactory $userFactory,
        UserUpdater $userUpdater,
        ValidatorInterface $userValidator,
        InMemoryGroupRepository $groupRepository
    ) {
        $this->userValidator = $userValidator;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @When a user is created with username :username
     */
    public function aUserIsCreatedWithIdentifier(string $username): void
    {
        $all = new Group('all');
        $this->groupRepository->save($all);

        $this->user = $this->userFactory->create();

        $userData = [
            'username' => $username,
            'groups' => ['all'],
        ];

        $this->userUpdater->update($this->user, $userData);
    }

    /**
     * @Then the error :errorMessage is raised
     *
     * @throws \Exception
     */
    public function theErrorIsRaised(string $errorMessage): void
    {
        $violations = $this->userValidator->validate($this->user);

        $messages = [];
        $isFoundMessage = false;
        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            $messages[] = $message;
            if ($message === $errorMessage) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(
                sprintf(
                    'Expected error message "%s" was not found, %s given', $errorMessage,
                    implode(',', $messages)
                )
            );
        }
    }
}
