<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\UserCatalogChannelResetter;

use Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserCatalogChannelResetter;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserCatalogChannelResetterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->changeUserCatalogChannel('tablet');
    }

    /**
     * @test
     */
    public function it_does_not_change_the_user_catalog_channel_if_the_channel_still_exist()
    {
        $this->assertUserCatalogChannel('tablet');
        $this->getResetter()->execute();
        $this->assertUserCatalogChannel('tablet');
    }

    /**
     * @test
     */
    public function it_changes_the_user_catalog_channel_to_default_ecommerce_if_the_locale_does_not_exist_anymore()
    {
        $this->assertUserCatalogChannel('tablet');
        $this->deleteChannel('tablet');

        $this->getResetter()->execute();
        $this->assertUserCatalogChannel('ecommerce');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getResetter(): UserCatalogChannelResetter
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserCatalogChannelResetter');
    }

    private function assertUserCatalogChannel(string $expectedCatalogChannel): void
    {
        $sql = <<<SQL
            SELECT pim_catalog_channel.code 
            FROM oro_user
            JOIN pim_catalog_channel ON oro_user.catalogScope_id = pim_catalog_channel.id
            WHERE username = 'admin'
        SQL;

        $actualCatalogChannel = $this->getConnection()->executeQuery($sql)->fetchOne();

        $this->assertEquals($expectedCatalogChannel, $actualCatalogChannel);
    }

    private function changeUserCatalogChannel(string $channelCode)
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
