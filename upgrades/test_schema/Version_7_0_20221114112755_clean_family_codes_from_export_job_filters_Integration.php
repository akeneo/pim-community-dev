<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221114112755_clean_family_codes_from_export_job_filters_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221114112755_clean_family_codes_from_export_job_filters';

    private Connection $connection;
    private int $dummyAttributeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->dummyAttributeId = $this->createDummyAttribute();
    }

    public function test_it_removes_non_existing_family_codes_from_export_filters(): void
    {
        $this->createFamily('mugs');
        $this->createFamily('shoes');

        $this->createFilteredExportJob('an_export', ['mugs', 'shoes', 'unknown']);
        $this->createFilteredExportJob('another_export', ['shoes']);
        $this->createFilteredExportJob('another_another_export', ['not_exist']);
        $this->createFilteredExportJob('an_export_without_family_filters');

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['mugs', 'shoes', 'unknown']);
        $this->assertJobContainsFilteredFamilyCodes('another_export', ['shoes']);
        $this->assertJobContainsFilteredFamilyCodes('another_another_export', ['not_exist']);
        $this->assertJobDoesNotContainFamilyFilter('an_export_without_family_filters');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['mugs', 'shoes']);
        $this->assertJobContainsFilteredFamilyCodes('another_export', ['shoes']);
        $this->assertJobDoesNotContainFamilyFilter('another_another_export');
        $this->assertJobDoesNotContainFamilyFilter('an_export_without_family_filters');
    }

    public function test_it_corrects_family_codes_from_export_filters(): void
    {
        $this->createFamily('MugS');
        $this->createFamily('Shoes');

        $this->createFilteredExportJob('an_export', ['mugs', 'Shoes']);
        $this->createFilteredExportJob('another_export', ['MugS', 'shoes']);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['mugs', 'Shoes']);
        $this->assertJobContainsFilteredFamilyCodes('another_export', ['MugS', 'shoes']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['MugS', 'Shoes']);
        $this->assertJobContainsFilteredFamilyCodes('another_export', ['MugS', 'Shoes']);
    }

    public function test_it_is_idempotent(): void
    {
        $this->createFamily('mugs');
        $this->createFamily('shoes');

        $this->createFilteredExportJob('an_export', ['mugs', 'shoes', 'unknown']);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['mugs', 'shoes', 'unknown']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['mugs', 'shoes']);
    }

    private function createFamily(string $familyCode): void
    {
        $sql = 'INSERT INTO pim_catalog_family (label_attribute_id, image_attribute_id, code, created, updated) VALUES (:dummy_attribute_id, :dummy_attribute_id, :code, NOW(), NOW())';
        $this->connection->executeStatement($sql, ['code' => $familyCode, 'dummy_attribute_id' => $this->dummyAttributeId]);
    }

    private function createFilteredExportJob(string $jobCode, array $filteredFamilyCodes = []): void
    {
        $rawParameters = [
            'storage' => [
                'type' => 'none',
                'file_path' => '/tmp/export_%job_label%_%datetime%.csv',
            ],
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
            'decimalSeparator' => '.',
            'dateFormat' => 'yyyy-MM-dd',
            'with_media' => true,
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => NULL,
            'filters' => [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'field' => 'categories',
                        'operator' => 'IN CHILDREN',
                        'value' => [
                            'master',
                        ],
                    ],
                    [
                        'field' => 'completeness',
                        'operator' => '>=',
                        'value' => 100,
                        'context' => [
                            'locales' => [
                                'fr_FR',
                                'en_US',
                                'de_DE',
                            ],
                        ],
                    ],
                ],
                'structure' => [
                    'scope' => 'mobile',
                    'locales' => [
                        'fr_FR',
                        'en_US',
                        'de_DE',
                    ],
                ],
            ],
        ];

        if(!empty($filteredFamilyCodes)) {
            $rawParameters['filters']['data'][] = [
                'field' => 'family',
                'operator' => 'IN',
                'value' => $filteredFamilyCodes,
            ];
        }

        $sql = <<<SQL
INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
VALUES (:code, :code, :code, 0, 'Dummy Connector', :raw_parameters, 'export')
SQL;

        $this->connection->executeStatement($sql, ['code' => $jobCode, 'raw_parameters' => serialize($rawParameters)]);
    }

    private function assertJobContainsFilteredFamilyCodes(string $jobCode, array $expectedFilteredFamilyCodes = []): void
    {
        $familyFilter = $this->fetchFamilyFilter($jobCode);
        $actualFilteredFamilyCodes = $familyFilter['value'];

        $this->assertEquals($expectedFilteredFamilyCodes, $actualFilteredFamilyCodes, 'Failed asserting that job contains filtered family codes');
    }

    private function assertJobDoesNotContainFamilyFilter(string $jobCode): void
    {
        $this->assertNull($this->fetchFamilyFilter($jobCode), 'Failed asserting that job does not contain family filter');
    }

    private function fetchFamilyFilter(string $jobCode): ?array
    {
        $sql = <<<SQL
SELECT raw_parameters
FROM akeneo_batch_job_instance
WHERE code = :code
SQL;

        $stmt = $this->connection->executeQuery($sql, ['code' => $jobCode]);
        $serializedRawParameters = $stmt->fetchOne();
        $rawParameters = unserialize($serializedRawParameters);

        $familyFilter = array_values(array_filter($rawParameters['filters']['data'], static fn (array $filter) => 'family' === $filter['field']));

        return !empty($familyFilter) ? $familyFilter[0] : null;
    }

    private function createDummyAttribute(): int
    {
        $createDummyAttributeGroupSql = <<<SQL
INSERT INTO pim_catalog_attribute_group (code, sort_order, created, updated) 
VALUES ('dummy_group', 1, NOW(), NOW()) 
SQL;

        $this->connection->executeQuery($createDummyAttributeGroupSql);
        $dummyAttributeGroupId = $this->connection->lastInsertId();

        $createDummyAttributeSql = <<<SQL
INSERT INTO pim_catalog_attribute (group_id, sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated)
VALUES (:group_id, 1, 0, 0, 0, 0, 'dummy_attribute', 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_text', 'text', NOW(), NOW())
SQL;

        $this->connection->executeQuery($createDummyAttributeSql, ['group_id' => $dummyAttributeGroupId]);

        return $this->connection->lastInsertId();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
