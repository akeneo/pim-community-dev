<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\EndToEnd\Connection;

use Akeneo\Apps\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Apps\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionEndToEnd extends TestCase
{
    public function test_it_creates_the_connection_and_client_and_user()
    {
        $createConnectionCommand = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $connectionWithCredentials = $this->get('akeneo_app.application.handler.create_app')->handle($createConnectionCommand);

        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertInstanceOf(ConnectionWithCredentials::class, $connectionWithCredentials);
        Assert::assertEquals('magento', $connectionWithCredentials->code());
        Assert::assertEquals('Magento Connector', $connectionWithCredentials->label());
        Assert::assertEquals(FlowType::DATA_DESTINATION, $connectionWithCredentials->flowType());
        Assert::assertNull($connectionWithCredentials->image());

        Assert::assertEquals(1, $this->countConnection('magento'));
        Assert::assertEquals(1, $this->countClient($connectionWithCredentials->secret()));

        $user = $this->selectUser($connectionWithCredentials->username());
        $regex = sprintf('/^%s_[0-9]{4}$/', $connectionWithCredentials->code());
        Assert::assertSame(1, preg_match($regex, $user['username']));
        Assert::assertSame(1, preg_match('/@example.com$/', $user['email']));
        Assert::assertSame($connectionWithCredentials->label(), $user['first_name']);
        Assert::assertSame(' ', $user['last_name']);
        Assert::assertSame(1, (int) $user['enabled']);
        Assert::assertSame(0, (int) $user['emailNotifications']);

        Assert::assertSame('ROLE_USER', $user['role']);
        Assert::assertSame('All', $user['group_name']);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function countConnection(string $connectionCode): int
    {
        $selectSql = <<<SQL
SELECT count(code) FROM akeneo_app WHERE code = :code
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['code' => $connectionCode]);

        return (int) $stmt->fetchColumn();
    }

    private function countClient(string $secret): int
    {
        $selectSql = <<<SQL
SELECT count(id) FROM pim_api_client WHERE secret = :secret
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['secret' => $secret]);

        return (int) $stmt->fetchColumn();
    }

    private function selectUser(string $username): array
    {
        $selectSql = <<<SQL
SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.enabled, u.emailNotifications, r.role, g.name as group_name
FROM oro_user u
INNER JOIN oro_user_access_role ur ON ur.user_id = u.id
INNER JOIN oro_access_role r ON r.id = ur.role_id
INNER JOIN oro_user_access_group ug ON ug.user_id = u.id
INNER JOIN oro_access_group g ON g.id = ug.group_id
WHERE u.username = :username
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['username' => $username]);

        return $stmt->fetchAll()[0];
    }

    private function getDbalConnection(): DbalConnection
    {
        return $this->get('database_connection');
    }
}
