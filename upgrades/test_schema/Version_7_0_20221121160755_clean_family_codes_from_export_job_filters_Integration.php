<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class Version_7_0_20221121160755_clean_family_codes_from_export_job_filters_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20221121160755_clean_family_codes_from_export_job_filters';

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_clean_value_in_family_filter(): void
    {
        $this->createFilteredExportJob('an_export_without_family_filter', []);
        $this->createFilteredExportJob('an_export_with_normal_family_filter', ['shoes', 'hat']);
        $this->createFilteredExportJob('an_export_with_non_sequential_family_filter', [1 => 'shoes', 2 => 'hat']);
        $this->createFilteredExportJob('an_export_with_object_in_family_filter', ['[object Object]', 'shoes']);
        $this->createFilteredExportJob('an_export_with_non_sequential_and_object_in_family_filter', [
            1 => '[object Object]',
            2 => 'shoes',
            4 => '[object Object]',
        ]);

        $this->assertJobDoesNotContainFamilyFilter('an_export_without_family_filter');
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_normal_family_filter', ['shoes', 'hat']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_non_sequential_family_filter', [1 => 'shoes', 2 => 'hat']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_object_in_family_filter', ['[object Object]', 'shoes']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_non_sequential_and_object_in_family_filter', [
            1 => '[object Object]',
            2 => 'shoes',
            4 => '[object Object]',
        ]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertJobDoesNotContainFamilyFilter('an_export_without_family_filter');
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_normal_family_filter', ['shoes', 'hat']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_non_sequential_family_filter', ['shoes', 'hat']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_object_in_family_filter', ['shoes']);
        $this->assertJobContainsFilteredFamilyCodes('an_export_with_non_sequential_and_object_in_family_filter', ['shoes']);
    }

    public function test_it_is_idempotent(): void
    {
        $this->createFilteredExportJob('an_export', [1 => 'shoes', 2 => 'hat']);
        $this->assertJobContainsFilteredFamilyCodes('an_export', [1 => 'shoes', 2 => 'hat']);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL, true);

        $this->assertJobContainsFilteredFamilyCodes('an_export', ['shoes', 'hat']);
    }

    private function createFilteredExportJob(string $jobCode, array $filteredFamilyCodes): void
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
VALUES (:code, :code, 'an_export', 0, 'Dummy Connector', :raw_parameters, 'export')
SQL;

        $this->connection->executeStatement($sql, ['code' => $jobCode, 'raw_parameters' => serialize($rawParameters)]);
    }

    private function assertJobContainsFilteredFamilyCodes(string $jobCode, array $expectedFilteredFamilyCodes = []): void
    {
        $familyFilter = $this->fetchFamilyFilter($jobCode);
        $actualFilteredFamilyCodes = $familyFilter['value'];

        $this->assertSame($expectedFilteredFamilyCodes, $actualFilteredFamilyCodes, 'Failed asserting that job contains filtered family codes');
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
