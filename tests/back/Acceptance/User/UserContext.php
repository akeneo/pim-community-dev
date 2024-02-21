<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserContext implements Context
{
    public function __construct(
        private readonly UserFactory $userFactory,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly InMemoryUserRepository $userRepository,
    ) {
    }

    /**
     * @Given /^an authentified administrator$/
     */
    public function anAuthenifiedAdministrator()
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();
        $user->setUsername('admin');
        $this->userRepository->save($user);

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMINISTRATOR']);
        $this->tokenStorage->setToken($token);
    }

    /**
     * @Given /^an authenticated user$/
     */
    public function anAuthenticatedUser()
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();
        $user->setUsername('julia');

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
    }
}
