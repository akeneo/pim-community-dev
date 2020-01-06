<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\User\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryUserPermissionsRepository;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUser implements CreateUserInterface
{
    /** @var InMemoryUserPermissionsRepository */
    private $inMemoryUserPermissionsRepository;

    public function __construct(InMemoryUserPermissionsRepository $inMemoryUserPermissionsRepository)
    {
        $this->inMemoryUserPermissionsRepository = $inMemoryUserPermissionsRepository;
    }

    public function execute(string $username, string $firstname, string $lastname): User
    {
        $user = new User(42, 'magento_app', 'pwd_app');

        $this->inMemoryUserPermissionsRepository->add($user, 'ROLE_USER', 'all');

        return $user;
    }
}
