<?php

declare(strict_types=1);


namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ImportAttributesIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_attribute_import';

    /** @test */
    public function test_it_imports_multiple_identifier_attributes(): void
    {
        $content = <<<CSV
type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;sort_order;decimals_allowed;negative_allowed;default_value
pim_catalog_identifier;identifier1;Identifier1;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier2;Identifier2;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier3;Identifier3;other;1;1;0;0;;;;1;;;
CSV;
        $this->getJobLauncher()->launchImport(static::CSV_IMPORT_JOB_CODE, $content);
        $warnings = $this->getWarnings();

        Assert::assertEmpty($warnings);

        $identifierAttribute1 = $this->getAttributeByCode('identifier1');
        $identifierAttribute2 = $this->getAttributeByCode('identifier2');
        $identifierAttribute3 = $this->getAttributeByCode('identifier3');

        Assert::assertNotNull($identifierAttribute1);
        Assert::assertNotNull($identifierAttribute2);
        Assert::assertNotNull($identifierAttribute3);
    }

    /** @test */
    public function test_it_ignores_the_is_main_identifier_column_if_present(): void
    {
        $content = <<<CSV
type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;sort_order;decimals_allowed;negative_allowed;default_value;is_main_identifier
pim_catalog_identifier;identifier1;Identifier1;other;1;1;0;0;;;;1;;;;1
CSV;
        $this->getJobLauncher()->launchImport(static::CSV_IMPORT_JOB_CODE, $content);
        $warnings = $this->getWarnings();

        Assert::assertCount(0, $warnings);
        Assert::assertFalse($this->getAttributeByCode('identifier1')->isMainIdentifier());
    }

    /** @test */
    public function test_it_renders_errors_when_adding_too_many_identifier_attributes(): void
    {
        $content = <<<CSV
type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;sort_order;decimals_allowed;negative_allowed;default_value
pim_catalog_identifier;identifier1;Identifier1;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier2;Identifier2;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier3;Identifier3;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier4;Identifier4;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier5;Identifier5;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier6;Identifier6;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier7;Identifier7;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier8;Identifier8;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier9;Identifier9;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier10;Identifier10;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier11;Identifier11;other;1;1;0;0;;;;1;;;
pim_catalog_identifier;identifier12;Identifier12;other;1;1;0;0;;;;1;;;
CSV;
        $this->getJobLauncher()->launchImport(static::CSV_IMPORT_JOB_CODE, $content);
        $warnings = $this->getWarnings();

        Assert::assertCount(3, $warnings);
        $warning = 'Limit of "10" identifier attributes is reached. The following identifier has not been created : Identifier10
';
        Assert::assertEquals($warning, $warnings[0]['reason']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_IMPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_IMPORT_JOB_CODE,
                'status' => 0,
                'type' => 'import',
                'raw_parameters' => 'a:5:{s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";}',
            ]
        );
    }

    private function getAttributeByCode(string $code): Attribute
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
    }

    private function getWarnings(): array
    {
        return $this->getConnection()->fetchAllAssociative('SELECT reason FROM akeneo_batch_warning;');
    }

    private function getJobLauncher()
    {
        return $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
