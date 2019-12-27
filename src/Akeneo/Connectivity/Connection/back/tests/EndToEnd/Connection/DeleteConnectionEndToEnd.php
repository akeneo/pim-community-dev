<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionEndToEnd extends ApiTestCase
{
    public function test_it_deletes_the_connection_and_client_and_user()
    {
        $createConnectionCommand = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $connectionWithCredentials = $this->get('akeneo_app.application.handler.create_connection')->handle($createConnectionCommand);

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $connectionWithCredentials->clientId(),
            $connectionWithCredentials->secret(),
            $connectionWithCredentials->username(),
            $connectionWithCredentials->password()
        );
        $apiClient->request('GET', 'api/rest/v1/attributes');

        $this->get('doctrine.orm.entity_manager')->clear();

        $deleteConnectionCommand = new DeleteConnectionCommand('magento');
        $this->get('akeneo_app.application.handler.delete_connection')->handle($deleteConnectionCommand);

        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $apiClient->getResponse()->getStatusCode());

        $countConnection = $this->countConnection($connectionWithCredentials->code());
        Assert::assertEquals(0, $countConnection);

        $countClient = $this->countClient($connectionWithCredentials->secret());
        Assert::assertEquals(0, $countClient);

        $countUser = $this->countUser($connectionWithCredentials->username());
        Assert::assertEquals(0, $countUser);
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
        $stmt = $this->get('database_connection')->executeQuery($selectSql, ['code' => $connectionCode]);

        return (int) $stmt->fetchColumn();
    }

    private function countClient(string $secret): int
    {
        $selectSql = <<<SQL
SELECT count(id) FROM pim_api_client WHERE secret = :secret
SQL;
        $stmt = $this->get('database_connection')->executeQuery($selectSql, ['secret' => $secret]);

        return (int) $stmt->fetchColumn();
    }

    private function countUser(string $username): int
    {
        $selectSql = <<<SQL
SELECT count(id) FROM oro_user WHERE username = :username
SQL;
        $stmt = $this->get('database_connection')->executeQuery($selectSql, ['username' => $username]);

        return (int) $stmt->fetchColumn();
    }
}
