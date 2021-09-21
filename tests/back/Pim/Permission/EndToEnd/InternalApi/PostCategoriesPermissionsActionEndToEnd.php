<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class PostCategoriesPermissionsActionEndToEnd extends WebTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = self::$container->get('database_connection');
    }

    public function testItSavesCategoriesPermissions(): void
    {
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            '/rest/permissions/category',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode([
                'user_group' => 'Redactor',
                'permissions' => [
                    'own' => [
                        'all' => false,
                        'identifiers' => [
                            'master',
                        ],
                    ],
                    'edit' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
            ])
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $defaultPermissions = $this->getUserGroupDefaultPermissions('Redactor');
        assert::assertEquals([
            'category_own' => false,
            'edit_own' => true,
            'view_own' => true,
        ], $defaultPermissions);
    }

    private function getUserGroupDefaultPermissions(string $name): array
    {
        $query = <<<SQL
SELECT default_permissions
FROM oro_access_group
WHERE name = :name
SQL;
        $result = $this->connection->fetchColumn($query, [
            'name' => $name,
        ]);

        return $result ?? [];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
