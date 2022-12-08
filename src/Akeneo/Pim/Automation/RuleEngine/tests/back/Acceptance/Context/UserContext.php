<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\Context;

use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Updater\RoleUpdater;
use Behat\Behat\Context\Context;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class UserContext implements Context
{
    /** @var InMemoryUserRepository */
    private $userRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(InMemoryUserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given /^I have permission to import rules$/
     * @Given /^I have permission to execute rules$/
     * @Given /^I have permission to export rules$/
     */
    public function iHavePermissionToImportRules()
    {
        $adminUser = new User();
        $adminUser->setId(-1);
        $adminUser->setUsername('admin');
        $this->userRepository->save($adminUser);

        $token = new UsernamePasswordToken($adminUser, 'main', $adminUser->getRoles());
        $this->tokenStorage->setToken($token);
    }
}
