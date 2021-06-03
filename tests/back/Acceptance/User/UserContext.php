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
    /** @var UserFactory */
    private $userFactory;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        UserFactory $userFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->userFactory = $userFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given /^an authentified administrator$/
     */
    public function anAuthenifiedAdministrator()
    {
        /** @var UserInterface $user */
        $user = $this->userFactory->create();
        $user->setUsername('admin');

        $token = new UsernamePasswordToken($user, null, 'main', ['ROLE_ADMINISTRATOR']);
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

        $token = new UsernamePasswordToken($user, null, 'main', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
    }
}
