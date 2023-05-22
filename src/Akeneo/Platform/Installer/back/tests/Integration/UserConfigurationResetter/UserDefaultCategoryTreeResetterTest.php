<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\UserConfigurationResetter;

use Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserDefaultCategoryTreeResetter;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserDefaultCategoryTreeResetterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->changeDefaultCategoryTree('categoryA');
    }

    /**
     * @test
     */
    public function it_does_not_change_the_user_default_category_tree_if_the_category_tree_still_exist()
    {
        $this->assertUserDefaultCategoryTree('categoryA');
        $this->getResetter()->execute();
        $this->assertUserDefaultCategoryTree('categoryA');
    }

    /**
     * @test
     */
    public function it_changes_the_user_default_category_tree_to_default_category_if_the_category_does_not_exist_anymore()
    {
        $this->assertUserDefaultCategoryTree('categoryA');
        $this->deleteCategoryTree('categoryA');

        $this->getResetter()->execute();
        $this->assertUserDefaultCategoryTree('master');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getResetter(): UserDefaultCategoryTreeResetter
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserDefaultCategoryTreeResetter');
    }

    private function assertUserDefaultCategoryTree(string $expectedCatalogLocale): void
    {
        $sql = <<<SQL
            SELECT pim_catalog_category.code 
            FROM oro_user
            JOIN pim_catalog_category ON oro_user.defaultTree_id = pim_catalog_category.id
            WHERE username = 'admin'
        SQL;

        $actualCatalogLocale = $this->getConnection()->executeQuery($sql)->fetchOne();

        $this->assertEquals($expectedCatalogLocale, $actualCatalogLocale);
    }

    private function changeDefaultCategoryTree(string $categoryTreeCode)
    {
        $sql = <<<SQL
            UPDATE oro_user
            SET defaultTree_id = (
                SELECT id 
                FROM pim_catalog_category
                WHERE code = :categoryTreeCode
            )
        SQL;

        $this->getConnection()->executeStatement($sql, ['categoryTreeCode' => $categoryTreeCode]);
    }

    private function deleteCategoryTree(string $categoryTreeCode): void
    {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql .= 'DELETE FROM pim_catalog_category WHERE code = :categoryTreeCode;';
        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;';

        $this->getConnection()->executeStatement($sql, ['categoryTreeCode' => $categoryTreeCode]);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
