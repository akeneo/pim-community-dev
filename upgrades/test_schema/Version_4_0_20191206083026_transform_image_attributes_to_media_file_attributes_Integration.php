<?php declare(strict_types=1);

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use \Doctrine\DBAL\Connection;

/**
 * Asset manager migration.
 */
final class Version_4_0_20191206083026_transform_image_attributes_to_media_file_attributes_Integration extends TestCase
{
    private const ASSET_FAMILY = 'packshot';
    private const ATTRIBUTE_IMAGE_1_IDENTIFIER = 'image1_packshot_d05292f9-2e09-401d-b306-c5ae8321e51b';
    private const ATTRIBUTE_IMAGE_2_IDENTIFIER = 'image2_packshot_d05292f9-2e09-401d-b306-c5ae8321e51b';
    private const OTHER_ATTRIBUTE_TYPE_ATTRIBUTE = 'other1_attribute_type_packshot_123123123';
    private const OTHER_ATTRIBUTE_TYPE = 'text';

    private const NEW_PROPERTY_KEY = 'media_type';
    private const NEW_PROPERTY_VALUE = 'image';
    private const NEW_ATTRIBUTE_TYPE = 'media_file';

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_adapts_image_attributes_from_3_2_to_media_file_attributes_introduced_on_master()
    {
        // Arrange
        $this->createAssetFamily();
        $this->createImageAttributes();
        $this->createOtherAttribute();

        // Act
        $this->runMigration();

        // Assert
        $migratedAttributes = $this->fetchMigratedAttributes();
        $this->assertAllImageAttributesAreNowMediaFileAttributes($migratedAttributes);
        $this->assertOtherAttributeIsUnchanged();
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel()
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    private function createAssetFamily(): void
    {
        $assetFamily = self::ASSET_FAMILY;
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO `akeneo_asset_manager_asset_family` (`identifier`, `labels`, `image`, `attribute_as_label`, `attribute_as_main_media`, `rule_templates`, `transformations`)
VALUES
	('$assetFamily', '{\"en_US\": \"azeaze\"}', NULL, NULL, NULL, '[]', '[]');
SQL
        );
    }

    private function createImageAttributes(): void
    {
        $image1 = self::ATTRIBUTE_IMAGE_1_IDENTIFIER;
        $image2 = self::ATTRIBUTE_IMAGE_2_IDENTIFIER;
        $assetFamily = self::ASSET_FAMILY;
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO `akeneo_asset_manager_attribute` 
    (`identifier`, `code`, `asset_family_identifier`, `labels`, `attribute_type`, `attribute_order`, `is_required`, `value_per_channel`, `value_per_locale`, `additional_properties`)
VALUES
	('$image1', 'image1', '$assetFamily', '[]', 'image', 1, 0, 0, 0, '{\"max_file_size\": null, \"allowed_extensions\": []}'),
	('$image2', 'image2', '$assetFamily', '[]', 'image', 2, 0, 0, 0, '{\"max_file_size\": null, \"allowed_extensions\": []}');
SQL
        );
    }

    private function denormalizeAdditionalProperties($result): array
    {
        return array_map(
            function ($attribute) {
                $attribute['additional_properties'] = json_decode($attribute['additional_properties'], true);

                return $attribute;
            },
            $result
        );
    }

    private function assertAllImageAttributesAreNowMediaFileAttributes(array $migratedAttributes): void
    {
        self::assertCount(2, $migratedAttributes);
        array_walk(
            $migratedAttributes,
            function ($attribute) {
                self::assertEquals(self::NEW_ATTRIBUTE_TYPE, $attribute['attribute_type']);
                self::assertArrayHasKey(self::NEW_PROPERTY_KEY, $attribute['additional_properties']);
                self::assertEquals(self::NEW_PROPERTY_VALUE, $attribute['additional_properties'][self::NEW_PROPERTY_KEY]);
            }
        );
    }

    private function fetchMigratedAttributes(): array
    {
        $selectImageAttributesForIdentifiers = <<<SQL
SELECT attribute_type, additional_properties
FROM akeneo_asset_manager_attribute
WHERE identifier IN (:attribute_identifiers);
SQL;
        $stmt = $this->get('database_connection')->executeQuery(
            $selectImageAttributesForIdentifiers,
            ['attribute_identifiers' => [self::ATTRIBUTE_IMAGE_1_IDENTIFIER, self::ATTRIBUTE_IMAGE_2_IDENTIFIER]],
            ['attribute_identifiers' =>  Connection::PARAM_STR_ARRAY]
        );
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = $this->denormalizeAdditionalProperties($result);

        return $result;
    }

    private function createOtherAttribute()
    {
        $updateStatement = <<<SQL
INSERT INTO `akeneo_asset_manager_attribute` 
    (`identifier`, `code`, `asset_family_identifier`, `labels`, `attribute_type`, `attribute_order`, `is_required`, `value_per_channel`, `value_per_locale`, `additional_properties`)
VALUES
	(:attribute_identifier, :other_attribute_type, :asset_family, '[]', 'text', 3, 0, 0, 0, '{\"max_file_size\": null, \"allowed_extensions\": []}')
	;
SQL;
        $this->get('database_connection')
             ->executeQuery(
                 $updateStatement,
                 [
                     'attribute_identifier' => self::OTHER_ATTRIBUTE_TYPE_ATTRIBUTE,
                     'asset_family'          => self::ASSET_FAMILY,
                     'other_attribute_type'  => self::OTHER_ATTRIBUTE_TYPE,
                 ]
             );
    }

    private function assertOtherAttributeIsUnchanged(): void
    {
        $selectImageAttributesForIdentifiers = <<<SQL
SELECT attribute_type, additional_properties
FROM akeneo_asset_manager_attribute
WHERE identifier = :attribute_identifier;
SQL;
        $stmt = $this->get('database_connection')->executeQuery(
            $selectImageAttributesForIdentifiers,
            ['attribute_identifier' => self::OTHER_ATTRIBUTE_TYPE_ATTRIBUTE]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        self::assertIsArray($result);
        self::assertEquals(self::OTHER_ATTRIBUTE_TYPE, $result['attribute_type']);
    }
}
