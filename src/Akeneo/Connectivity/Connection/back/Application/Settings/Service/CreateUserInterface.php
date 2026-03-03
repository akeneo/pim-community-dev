<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Service;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateUserInterface
{
    /**
     * Creates and persists a new user
     *
     * @param string[]|null $groups
     * @param string[]|null $roles
     * @return User
     */
    public function execute(string $username, string $firstname, string $lastname, ?array $groups = null, ?array $roles = null): User;
}
