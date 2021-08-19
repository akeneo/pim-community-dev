<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Webmozart\Assert\Assert;

final class Version_6_0_20210818141014_add_source_concatenation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210818141014_add_source_concatenation';
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_add_source_in_concat_element_list(): void
    {
        $this->createTailoredExportWithColumns();
        $this->assertConcatElementListContainUuid([
            '7ea61c65-ea3b-46a3-8322-1f4fd9c4cd7c' => [],
            '25107e67-8c36-454e-a425-f419a2f855ae' => [],
            '283bce60-e958-4c34-8eb1-40858482c8bf' => [],
            '7af8c16b-4ef6-459d-813f-2b0cba82fc34' => []
        ]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertConcatElementListContainUuid([
            '7ea61c65-ea3b-46a3-8322-1f4fd9c4cd7c' => ['65df43a4-c768-4fda-9733-f6952285aeed'],
            '25107e67-8c36-454e-a425-f419a2f855ae' => ['0ce274a1-d028-438a-9ce9-a676bfe41e20'],
            '283bce60-e958-4c34-8eb1-40858482c8bf' => ['cb4ef2bd-cb03-4976-90f0-37c1cefcf0d5', '7d0b61e5-d4c7-4dae-a532-b96841bc726e'],
            '7af8c16b-4ef6-459d-813f-2b0cba82fc34' => [
                '242ed452-d06b-4257-8dee-1e2436c99ba5',
                '50de19a7-8037-4a63-a6f3-3ecc7091ad2e',
                'c7a28fa3-0ee3-4ff0-ae9e-f4531c85e364',
                '64a3db42-6042-496b-b57b-62852b9fdea4'
            ]
        ]);
    }

    public function test_it_does_nothing_when_there_is_no_columns(): void
    {
        $this->createTailoredExportWithoutColumns();
        $this->assertConcatElementListContainUuid([]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertConcatElementListContainUuid([]);
    }

    public function test_it_does_nothing_when_there_is_no_tailored_export(): void
    {
        $this->assertConcatElementListContainUuid([]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertConcatElementListContainUuid([]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertConcatElementListContainUuid(array $expectedUuid)
    {
        $findJobInstance = <<<SQL
            SELECT raw_parameters 
            FROM akeneo_batch_job_instance 
            WHERE code = 'tailored_export';
        SQL;

        $normalizedRawParameters = $this->get('database_connection')->executeQuery($findJobInstance)->fetchColumn();
        if (false === $normalizedRawParameters) {
            Assert::isEmpty($expectedUuid);
            return;
        }

        $rawParameters = json_decode(unserialize($normalizedRawParameters), true);

        $sourceUuidInConcatenation = [];
        foreach ($rawParameters['columns'] as $column) {
            $sourceUuidInConcatenation[$column['uuid']] = array_map(fn($element) => $element['uuid'], $column['format']['elements']);
        }

        $this->assertEquals($expectedUuid, $sourceUuidInConcatenation);
    }

    private function createTailoredExportWithoutColumns()
    {
        $this->createTailoredExport('{"filePath":"\/tmp\/export_%job_label%_%datetime%.xlsx","withHeader":true,"linesPerFile":10000,"user_to_notify":null,"is_user_authenticated":false,"with_media":true,"columns":[],"filters":{"data":[{"field":"enabled","operator":"=","value":true},{"field":"categories","operator":"NOT IN","value":[]},{"field":"completeness","operator":"ALL","value":100}]}}');
    }

    private function createTailoredExportWithColumns()
    {
        $this->createTailoredExport('{"filePath":"\/tmp\/export_%job_label%_%datetime%.xlsx","withHeader":true,"linesPerFile":10000,"user_to_notify":null,"is_user_authenticated":false,"with_media":true,"columns":[{"uuid":"7ea61c65-ea3b-46a3-8322-1f4fd9c4cd7c","target":"sku","sources":[{"uuid":"65df43a4-c768-4fda-9733-f6952285aeed","code":"sku","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"25107e67-8c36-454e-a425-f419a2f855ae","target":"description","sources":[{"uuid":"0ce274a1-d028-438a-9ce9-a676bfe41e20","code":"description","type":"attribute","locale":"de_DE","channel":"ecommerce","operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"283bce60-e958-4c34-8eb1-40858482c8bf","target":"weight","sources":[{"uuid":"cb4ef2bd-cb03-4976-90f0-37c1cefcf0d5","code":"weight","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"value"}},{"uuid":"7d0b61e5-d4c7-4dae-a532-b96841bc726e","code":"weight","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"unit_code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"7af8c16b-4ef6-459d-813f-2b0cba82fc34","target":"concatenation","sources":[{"uuid":"242ed452-d06b-4257-8dee-1e2436c99ba5","code":"name","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"50de19a7-8037-4a63-a6f3-3ecc7091ad2e","code":"size","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"c7a28fa3-0ee3-4ff0-ae9e-f4531c85e364","code":"main_color","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"64a3db42-6042-496b-b57b-62852b9fdea4","code":"brand","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}}],"filters":{"data":[{"field":"enabled","operator":"=","value":true},{"field":"categories","operator":"NOT IN","value":[]},{"field":"completeness","operator":"ALL","value":100}]}}');
    }

    private function createTailoredExport(string $rawParameters)
    {
        $sql = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`id`, `code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	(158, 'tailored_export', 'Tailored Export', 'xlsx_tailored_product_export', 0, 'Akeneo Tailored Export', :raw_parameters, 'export');
SQL;

        $this->connection->executeUpdate($sql, [
            'raw_parameters' => serialize($rawParameters)
        ]);
    }
}
