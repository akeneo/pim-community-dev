<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Integration\UserConfigurationResetter;

use Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserUiLocaleResetter;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserUiLocaleResetterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->changeUiLocale('fr_FR');
    }

    /**
     * @test
     */
    public function it_does_not_change_the_user_ui_locale_if_the_locale_still_exist()
    {
        $this->assertUserUiLocale('fr_FR');
        $this->getResetter()->execute();
        $this->assertUserUiLocale('fr_FR');
    }

    /**
     * @test
     */
    public function it_changes_the_user_ui_locale_to_default_locale_if_the_locale_does_not_exist_anymore()
    {
        $this->assertUserUiLocale('fr_FR');
        $this->deleteLocale('fr_FR');

        $this->getResetter()->execute();
        $this->assertUserUiLocale('en_US');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getResetter(): UserUiLocaleResetter
    {
        return $this->get('Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter\UserUiLocaleResetter');
    }

    private function assertUserUiLocale(string $expectedCatalogLocale): void
    {
        $sql = <<<SQL
            SELECT pim_catalog_locale.code 
            FROM oro_user
            JOIN pim_catalog_locale ON oro_user.ui_locale_id = pim_catalog_locale.id
            WHERE username = 'admin'
        SQL;

        $actualCatalogLocale = $this->getConnection()->executeQuery($sql)->fetchOne();

        $this->assertEquals($expectedCatalogLocale, $actualCatalogLocale);
    }

    private function changeUiLocale(string $localeCode)
    {
        $sql = <<<SQL
            UPDATE oro_user
            SET ui_locale_id = (
                SELECT id 
                FROM pim_catalog_locale
                WHERE code = :localeCode
            )
        SQL;

        $this->getConnection()->executeStatement($sql, ['localeCode' => $localeCode]);
    }

    private function deleteLocale(string $localeCode): void
    {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql .= 'DELETE FROM pim_catalog_locale WHERE code = :localeCode;';
        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;';

        $this->getConnection()->executeStatement($sql, ['localeCode' => $localeCode]);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
