<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocaleFixturesLoader;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class SaveLocalesPermissionsActionEndToEnd extends WebTestCase
{
    private Connection $connection;
    private LocaleFixturesLoader $localeFixturesLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = self::$container->get('database_connection');
        $this->localeFixturesLoader = $this->get('akeneo_integration_tests.loader.locale');
    }

    public function testItSavesLocalesPermissions(): void
    {
        $this->get('feature_flags')->enable('permission');
        $this->authenticateAsAdmin();

        $this->localeFixturesLoader->activateLocalesOnChannel(['en_US', 'fr_FR', 'de_DE'], 'ecommerce');

        $this->client->request(
            'POST',
            '/rest/permissions/locale',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'user_group' => 'Redactor',
                'permissions' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['en_US', 'fr_FR'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $defaultPermissions = $this->getUserGroupDefaultPermissions('Redactor');
        Assert::assertEquals([
            'locale_edit' => false,
            'locale_view' => true,
        ], $defaultPermissions);

        $enPermissions = $this->getLocaleAccessFor('en_US');
        Assert::assertEquals(['edit' => true, 'view' => true], $enPermissions);

        $frPermissions = $this->getLocaleAccessFor('fr_FR');
        Assert::assertEquals(['edit' => true, 'view' => true], $frPermissions);

        $dePermissions = $this->getLocaleAccessFor('de_DE');
        Assert::assertEquals(['edit' => false, 'view' => true], $dePermissions);
    }

    public function testItDoesNotSavesLocalesPermissionsWhenFeatureDisabled(): void
    {
        $this->get('feature_flags')->disable('permission');
        $this->authenticateAsAdmin();

        $this->localeFixturesLoader->activateLocalesOnChannel(['en_US', 'fr_FR', 'de_DE'], 'ecommerce');

        $this->client->request(
            'POST',
            '/rest/permissions/locale',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                'user_group' => 'Redactor',
                'permissions' => [
                    'edit' => [
                        'all' => false,
                        'identifiers' => ['en_US', 'fr_FR'],
                    ],
                    'view' => [
                        'all' => true,
                        'identifiers' => [],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)
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

    private function getLocaleAccessFor(string $localeCode): ?array
    {
        $query = <<<SQL
SELECT
       pimee_security_locale_access.view_products as view,
       pimee_security_locale_access.edit_products as edit
FROM pim_catalog_locale
JOIN pimee_security_locale_access on pim_catalog_locale.id = pimee_security_locale_access.locale_id
JOIN oro_access_group on pimee_security_locale_access.user_group_id = oro_access_group.id
WHERE pim_catalog_locale.code = :locale_code 
    AND pim_catalog_locale.is_activated = 1
    AND oro_access_group.name = :user_group_name
LIMIT 1
SQL;

        $permissions = $this->connection->fetchAssociative($query, [
            'user_group_name' => 'Redactor',
            'locale_code' => $localeCode,
        ]) ?: null;

        if (!$permissions) {
            return null;
        }

        return \array_map(fn (int $permissionFlag): bool => (bool) $permissionFlag, $permissions);
    }
}
