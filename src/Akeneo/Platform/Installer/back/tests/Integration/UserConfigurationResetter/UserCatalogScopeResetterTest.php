<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\UserConfigurationResetter;

use Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserCatalogScopeResetter;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserCatalogScopeResetterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->changeUserCatalogScope('tablet');
    }

    /**
     * @test
     */
    public function it_does_not_change_the_user_catalog_locale_if_the_locale_still_exist()
    {
        $this->assertUserCalogScope('tablet');
        $this->getResetter()->execute();
        $this->assertUserCalogScope('tablet');
    }

    /**
     * @test
     */
    public function it_changes_the_user_catalog_locale_to_default_catalog_locale_if_the_locale_does_not_exist()
    {
        $this->assertUserCalogScope('tablet');
        $this->deleteChannel('tablet');

        $this->getResetter()->execute();
        $this->assertUserCalogScope('ecommerce');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getResetter(): UserCatalogScopeResetter
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserCatalogScopeResetter');
    }

    private function assertUserCalogScope(string $expectedCatalogScope): void
    {
        $sql = <<<SQL
            SELECT pim_catalog_channel.code 
            FROM oro_user
            JOIN pim_catalog_channel ON oro_user.catalogScope_id = pim_catalog_channel.id
            WHERE username = 'admin'
        SQL;

        $actualCatalogScope = $this->getConnection()->executeQuery($sql)->fetchOne();

        $this->assertEquals($expectedCatalogScope, $actualCatalogScope);
    }

    private function changeUserCatalogScope(string $channelCode)
    {
        $sql = <<<SQL
            UPDATE oro_user
            SET catalogScope_id = (
                SELECT id 
                FROM pim_catalog_channel
                WHERE code = :channelCode
            )
        SQL;

        $this->getConnection()->executeStatement($sql, ['channelCode' => $channelCode]);
    }

    private function deleteChannel(string $channelCode): void
    {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql .= 'DELETE FROM pim_catalog_channel WHERE code = :channelCode;';
        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;';

        $this->getConnection()->executeStatement($sql, ['channelCode' => $channelCode]);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
