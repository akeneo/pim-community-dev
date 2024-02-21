<?php


namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class Version_6_0_20210330143635_sanitize_users_and_channels_having_link_to_subcategory_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210330143635_sanitize_users_and_channels_having_link_to_subcategory';

    public function test_it_sanitizes_users_having_link_to_subcategory()
    {
        $this->aSubCategory();
        $this->aUserHavingLinkToSubCategory();

        Assert::count($this->findUsersHavingLinkToSubCategory(), 1);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::count($this->findUsersHavingLinkToSubCategory(), 0);
    }

    public function test_it_sanitizes_channels_having_link_to_subcategory()
    {
        $this->aSubCategory();
        $this->aChannelHavingLinkToSubCategory();

        Assert::count($this->findChannelsHavingLinkToSubCategory(), 1);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::count($this->findChannelsHavingLinkToSubCategory(), 0);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function findUsersHavingLinkToSubCategory(): array
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
            SELECT ou.id
            FROM oro_user ou
                     INNER JOIN pim_catalog_category pcc on ou.defaultTree_id = pcc.id
            WHERE pcc.parent_id IS NOT NULL
        SQL)->fetchAllAssociative();
    }

    private function findChannelsHavingLinkToSubCategory(): array
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
            SELECT pc_ch.id
            FROM pim_catalog_channel pc_ch
                 INNER JOIN pim_catalog_category pc_cat on pc_ch.category_id = pc_cat.id
            WHERE pc_cat.parent_id IS NOT NULL
        SQL)->fetchAllAssociative();
    }

    private function aSubCategory(): void
    {
        $masterCategoryId = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT id FROM pim_catalog_category pcc 
            WHERE pcc.code = 'master'
        SQL)->fetchOne();

        $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO pim_catalog_category (parent_id, code, created, root, lvl, lft, rgt) 
            VALUES (:parentId, 'aSubCategory', NOW(), :parentId, 1, 2, 7);
        SQL, ['parentId' => $masterCategoryId]);
    }

    private function aUserHavingLinkToSubCategory(): void
    {
        $subCategoryId = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT id FROM pim_catalog_category pcc 
            WHERE pcc.code = 'aSubCategory'
        SQL)->fetchOne();

        $localeId = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT id FROM pim_catalog_locale pcl 
            WHERE pcl.code = 'en_US'
        SQL)->fetchOne();

        $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO oro_user (ui_locale_id, username, email, enabled, salt, password, login_count, createdAt, updatedAt, emailNotifications, timezone, user_type, properties, defaultTree_id) 
            VALUES (:localeId, 'aUsername', 'a.username0@example.com',  1, 'salt', 'password', 0, NOW(), NOW(), 0, 'UTC', 'user', '[]', :rootId);
        SQL, ['rootId' => $subCategoryId, 'localeId' => $localeId]);
    }

    private function aChannelHavingLinkToSubCategory(): void
    {
        $subCategoryId = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT id FROM pim_catalog_category pcc 
            WHERE pcc.code = 'aSubCategory'
        SQL)->fetchOne();

        $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO pim_catalog_channel (category_id, code, conversionUnits) 
            VALUES (:rootId, 'aChannel', '[]');
        SQL, ['rootId' => $subCategoryId]);
    }
}
