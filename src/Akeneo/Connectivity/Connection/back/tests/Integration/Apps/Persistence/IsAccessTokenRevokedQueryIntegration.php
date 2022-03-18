<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\IsAccessTokenRevokedQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsAccessTokenRevokedQueryIntegration extends TestCase
{
    private Connection $connection;
    private IsAccessTokenRevokedQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(IsAccessTokenRevokedQuery::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItSavesAccessTokensOfAnAppInTheRevokedTable(): void
    {
        $this->saveRevokedAccessToken('revoked_access_token');

        $notRevoked = $this->query->execute('something_else');
        Assert::assertEquals(false, $notRevoked);

        $revoked = $this->query->execute('revoked_access_token');
        Assert::assertEquals(true, $revoked);
    }

    private function saveRevokedAccessToken(string $token): void
    {
        $query = <<<SQL
            INSERT INTO akeneo_connectivity_revoked_app_token (`token`)
            VALUES (:token)
            SQL;

        $this->connection->executeQuery($query, [
            'token' => $token,
        ]);
    }
}
