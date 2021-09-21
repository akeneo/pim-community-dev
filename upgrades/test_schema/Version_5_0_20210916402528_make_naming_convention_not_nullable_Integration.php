<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20210916402528_make_naming_convention_not_nullable_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20210916402528_make_naming_convention_not_nullable';
    private const NAMING_CONVENTION = [
        'source' => [
            'property' => 'code',
            'locale' => null,
            'channel' => null,
        ],
        'pattern' => '/^(<?product_ref>\w+)-(<?attribute>\w+).png$/',
        'abort_asset_creation_on_error' => false,
    ];

    /**
	 * @test
	 */
	public function it_modify_nullable_naming_convention_of_the_asset_family_table(): void
	{
		$this->makeColumnNullable();
		$this->createAssetFamilyWithoutNamingConvention();

		$this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertEquals([], $this->getAssetFamilyNamingConvention());
	}

    /**
     * @test
     */
    public function it_does_not_modify_existing_naming_convention(): void
    {
        $this->makeColumnNullable();
        $this->createAssetFamilyWithNamingConvention();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertEquals(self::NAMING_CONVENTION, $this->getAssetFamilyNamingConvention());
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAssetFamilyWithoutNamingConvention(): void
	{
		$this->getConnection()->executeQuery(<<<SQL
            INSERT INTO `akeneo_asset_manager_asset_family` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_main_media`, `rule_templates`, `transformations`)
            VALUES
                ('packshot', '[]', NULL, NULL, NULL, '[]', '[]');
            SQL
		);
	}

    private function createAssetFamilyWithNamingConvention(): void
    {
        $query = <<<SQL
            INSERT INTO `akeneo_asset_manager_asset_family` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_main_media`, `rule_templates`, `transformations`, `naming_convention`)
            VALUES
                ('packshot', '[]', NULL, NULL, NULL, '[]', '[]', :naming_convention);
        SQL;

        $this->getConnection()->executeQuery($query, ['naming_convention' => json_encode(self::NAMING_CONVENTION)]);
    }

    private function makeColumnNullable(): void
	{
		$this->getConnection()
			->executeQuery('ALTER TABLE akeneo_asset_manager_asset_family MODIFY `naming_convention` json;');
	}

    private function getAssetFamilyNamingConvention(): array
    {
        $result = $this->getConnection()
            ->executeQuery('SELECT naming_convention FROM akeneo_asset_manager_asset_family WHERE identifier = "packshot"')
            ->fetch();

        return json_decode($result['naming_convention'], true);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
