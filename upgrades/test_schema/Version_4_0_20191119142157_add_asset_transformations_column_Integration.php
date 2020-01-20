<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;

/**
 * This migration will ad a column "transformations" to the asset family table.
 */
final class Version_4_0_20191119142157_add_asset_transformations_column_Integration extends TestCase
{
	const ASSET_FAMILY = 'packshot';

	/**
	 * @inheritDoc
	 */
	protected function getConfiguration()
	{
		return $this->catalog->useMinimalCatalog();
	}

	/**
	 * @test
	 */
	public function it_adds_the_transformations_column_to_the_asset_family_table(): void
	{
		$this->dropTransformationsColumn();
		$this->createAssetFamily();

		$this->runMigration();

		$this->assertTransformationColumnExists();
		$this->assertTransformationIsAnEmptyArray();
	}

	private function runMigration(): void
	{
		$migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
		$this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
	}

	private function getMigrationLabel(): string
	{
		$migration = (new \ReflectionClass($this))->getShortName();
		$migration = str_replace('_Integration', '', $migration);
		$migration = str_replace('Version', '', $migration);

		return $migration;
	}

	private function createAssetFamily(): void
	{
		$this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO `akeneo_asset_manager_asset_family` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_main_media`, `rule_templates`, `naming_convention`)
VALUES
	('packshot', '[]', NULL, NULL, NULL, '[]', '[]');
SQL
		);
	}

	private function dropTransformationsColumn(): void
	{
		$this->get('database_connection')
			->executeQuery('ALTER TABLE akeneo_asset_manager_asset_family DROP COLUMN transformations;');
	}

	private function assertTransformationColumnExists(): void
	{
		$isFound = $this->get('database_connection')
			->executeQuery('SHOW COLUMNS FROM akeneo_asset_manager_asset_family LIKE "transformations";')
			->rowCount();

		self::assertEquals(1, $isFound, 'the "transformations" column of the akeneo_asset_manager_asset_family was not found.');
	}

	private function assertTransformationIsAnEmptyArray(): void
	{
		$result = $this->get('database_connection')
			->executeQuery(sprintf('SELECT transformations FROM akeneo_asset_manager_asset_family WHERE identifier = \'%s\';', self::ASSET_FAMILY))
			->fetch();

		self::assertEquals('[]', $result['transformations'], 'The transformation column does not have a default array as value when the asset family is migrated.');
	}
}
