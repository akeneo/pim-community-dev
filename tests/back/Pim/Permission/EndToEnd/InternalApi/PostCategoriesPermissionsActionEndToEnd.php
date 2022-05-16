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
        $this->get('feature_flags')->enable('permission');
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            '/rest/permissions/category',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
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
            'category_edit' => true,
            'category_view' => true,
        ], $defaultPermissions);

        $masterPermissions = $this->getCategoryAccessByUserGroup('Redactor', 'master');
        assert::assertEquals([
            'own' => true,
            'edit' => true,
            'view' => true,
        ], $masterPermissions);
    }

    public function testItDoesNotSavesCategoriesPermissionsWhenFeatureDisabled(): void
    {
        $this->get('feature_flags')->disable('permission');
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            '/rest/permissions/category',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
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

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    private function getUserGroupDefaultPermissions(string $name): array
    {
        $query = <<<SQL
SELECT default_permissions
FROM oro_access_group
WHERE name = :name
SQL;
        $result = $this->connection->fetchOne($query, [
            'name' => $name,
        ]);

        return \json_decode($result, true) ?? [];
    }

    private function getCategoryAccessByUserGroup(string $userGroupName, string $categoryCode): ?array
    {
        $query = <<<SQL
SELECT view_items AS view, edit_items AS edit, own_items AS own
FROM pimee_security_product_category_access
JOIN oro_access_group oag on pimee_security_product_category_access.user_group_id = oag.id
JOIN pim_catalog_category pcc on pimee_security_product_category_access.category_id = pcc.id
WHERE oag.name = :user_group_name
AND pcc.code = :category_code
SQL;

        $permissions = $this->connection->fetchAssociative($query, [
            'user_group_name' => $userGroupName,
            'category_code' => $categoryCode,
        ]) ?: null;

        if (!$permissions) {
            return null;
        }

        return \array_map(fn($v) => (bool) $v, $permissions);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
