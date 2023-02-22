<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\DeleteCustomAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\UpdateCustomAppSecretQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\UpdateCustomAppSecretQuery
 */
class UpdateCustomAppSecretQueryIntegration extends TestCase
{
    private ?CustomAppLoader $customAppLoader;
    private ?UpdateCustomAppSecretQuery $updateCustomAppSecretQuery;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppLoader = $this->get(CustomAppLoader::class);
        $this->updateCustomAppSecretQuery = $this->get(UpdateCustomAppSecretQuery::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_update_a_custom_app_secret(): void
    {
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $this->createAdminUser()->getId());

        $this->updateCustomAppSecretQuery->execute(
            clientId: '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            clientSecret: 'NjNjZWMxODc2Mjg2ZTkxMjEyYmI3NDMwNzkxZjRkNjQ3MTA5MDdjNWQzMWMzOTU3MTE1YWYxYWVjNDY0MWUwOQ'
        );

        $this->assertSame(
            $this->getCustomAppSecret('100eedac-ff5c-497b-899d-e2d64b6c59f9'),
            'NjNjZWMxODc2Mjg2ZTkxMjEyYmI3NDMwNzkxZjRkNjQ3MTA5MDdjNWQzMWMzOTU3MTE1YWYxYWVjNDY0MWUwOQ'
        );
    }

    public function test_it_does_nothing_on_unknown_id(): void
    {
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $this->createAdminUser()->getId());

        $preQuerySecret = $this->getCustomAppSecret('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->updateCustomAppSecretQuery->execute(
            clientId: 'wrong_id',
            clientSecret: 'NjNjZWMxODc2Mjg2ZTkxMjEyYmI3NDMwNzkxZjRkNjQ3MTA5MDdjNWQzMWMzOTU3MTE1YWYxYWVjNDY0MWUwOQ'
        );

        $postQuerySecret = $this->getCustomAppSecret('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertSame(
            $preQuerySecret,
            $postQuerySecret
        );
    }

    private function getCustomAppSecret(string $clientId): string
    {
        $sql = <<<SQL
        SELECT client_secret
        FROM akeneo_connectivity_test_app
        WHERE client_id = :clientId
        SQL;

        return $this->connection->fetchOne($sql, ['clientId' => $clientId]);
    }
}
