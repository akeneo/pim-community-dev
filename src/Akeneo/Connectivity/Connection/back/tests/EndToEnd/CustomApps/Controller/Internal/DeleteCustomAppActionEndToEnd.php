<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\DeleteCustomAppAction
 */
class DeleteCustomAppActionEndToEnd extends WebTestCase
{
    private ?Connection $connection;
    private ?CustomAppLoader $customAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->customAppLoader = $this->get(CustomAppLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_successfully_deletes_the_custom_app(): void
    {
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        Assert::assertEquals(1, $this->countCustomApps());

        $this->client->request(
            'DELETE',
            '/rest/custom-apps/100eedac-ff5c-497b-899d-e2d64b6c59f9',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertEquals(0, $this->countCustomApps());
    }

    private function countCustomApps(): int
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_connectivity_test_app
        SQL;

        return (int) $this->connection->fetchOne($query);
    }
}
