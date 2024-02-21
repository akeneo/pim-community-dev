<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveRevokedAccessTokensOfDisconnectedAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveRevokedAccessTokensOfDisconnectedAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private SaveRevokedAccessTokensOfDisconnectedAppQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(SaveRevokedAccessTokensOfDisconnectedAppQuery::class);
        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItSavesAccessTokensOfAnAppInTheRevokedTable(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
        );
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d146',
            'shopify',
        );

        $this->assertNoAccessTokensAreRevoked();

        $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        $this->assertAccessTokensAreSavedInTheRevokedTable([
            'magento', // The ConnectedAppLoader always create an access token using the app code
        ]);
    }

    private function assertNoAccessTokensAreRevoked(): void
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_connectivity_revoked_app_token
SQL;

        Assert::assertSame(0, (int) $this->connection->fetchOne($query));
    }

    private function assertAccessTokensAreSavedInTheRevokedTable(array $tokens): void
    {
        $query = <<<SQL
SELECT token
FROM akeneo_connectivity_revoked_app_token
ORDER BY token
SQL;

        Assert::assertSame($tokens, $this->connection->fetchFirstColumn($query));
    }
}
