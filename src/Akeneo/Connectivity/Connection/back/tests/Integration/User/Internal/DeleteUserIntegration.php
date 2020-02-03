<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\User\Internal;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\CreateUser;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\DeleteUser;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteUserIntegration extends TestCase
{
    public function test_it_deletes_a_user()
    {
        $user = $this->getCreateUserService()->execute('pimgento', 'Pimgento', ' ');

        $this->getDeleteUserService()->execute(new UserId($user->id()));

        $results = $this->getDatabaseConnection()->fetchAll('SELECT username FROM oro_user');
        Assert::assertCount(0, $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCreateUserService(): CreateUser
    {
        return $this->get('akeneo_connectivity.connection.service.user.create_user');
    }

    private function getDeleteUserService(): DeleteUser
    {
        return $this->get('akeneo_connectivity.connection.service.user.delete_user');
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
