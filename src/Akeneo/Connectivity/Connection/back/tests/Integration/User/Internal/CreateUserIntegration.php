<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\User\Internal;

use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\CreateUser;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserIntegration extends TestCase
{
    public function test_it_creates_a_user()
    {
        $user = $this->getCreateUserService()->execute('pimgento', 'Pimgento', ' ');

        Assert::assertInstanceOf(\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User::class, $user);
        Assert::assertRegExp('/^pimgento_[0-9]{4}$/', $user->username());

        $sqlQuery = <<<SQL
SELECT username, first_name, last_name, email, user_type, enabled FROM oro_user
SQL;
        $results = $this->getDatabaseConnection()->fetchAll($sqlQuery);
        Assert::assertCount(1, $results);

        Assert::assertEquals('Pimgento', $results[0]['first_name']);
        Assert::assertEquals(' ', $results[0]['last_name']);
        Assert::assertRegExp('/^pimgento_[0-9]{4}$/', $results[0]['username']);
        Assert::assertEquals(sprintf('%s@example.com', $results[0]['username']), $results[0]['email']);
        Assert::assertEquals(User::TYPE_API, $results[0]['user_type']);
        Assert::assertEquals(true, $results[0]['enabled']);
    }

    public function test_it_creates_a_user_fixing_incorrect_firstname_or_lastname()
    {
        $user = $this->getCreateUserService()->execute('pimgento', 'Pim&Ecom', 'Pim&Ecom');

        Assert::assertInstanceOf(\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User::class, $user);

        $sqlQuery = <<<SQL
SELECT username, first_name, last_name, email, user_type, enabled FROM oro_user
SQL;
        $results = $this->getDatabaseConnection()->fetchAll($sqlQuery);
        Assert::assertCount(1, $results);

        Assert::assertEquals('Pim_Ecom', $results[0]['first_name']);
        Assert::assertEquals('Pim_Ecom', $results[0]['last_name']);
        Assert::assertRegExp('/^pimgento_[0-9]{4}$/', $results[0]['username']);
        Assert::assertEquals(sprintf('%s@example.com', $results[0]['username']), $results[0]['email']);
        Assert::assertEquals(User::TYPE_API, $results[0]['user_type']);
        Assert::assertEquals(true, $results[0]['enabled']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCreateUserService(): CreateUser
    {
        return $this->get('akeneo_connectivity.connection.service.user.create_user');
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
