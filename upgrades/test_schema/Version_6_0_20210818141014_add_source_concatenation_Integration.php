<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\DBAL\Connection;

final class Version_6_0_20210818141014_add_source_concatenation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210818141014_add_source_concatenation';

    private Connection $connection;
    private JobInstanceRepository $jobInstanceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->jobInstanceRepository = $this->get('akeneo_batch.job.job_instance_repository');
    }

    public function test_it_adds_source_in_concat_element_list(): void
    {
        $this->createTailoredExportWithColumns();
        $this->assertConcatElementListContainUuid([
            'sku-column-uuid' => [],
            'description-column-uuid' => [],
            'weight-column-uuid' => [],
            'title-column-uuid' => []
        ]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertConcatElementListContainUuid([
            'sku-column-uuid' => ['sku-source-uuid'],
            'description-column-uuid' => ['description-de_DE-source-uuid'],
            'weight-column-uuid' => ['weight-value-source-uuid', 'weight-unit-source-uuid'],
            'title-column-uuid' => [
                'name-source-uuid',
                'size-source-uuid',
                'main_color-source-uuid',
                'brand-source-uuid'
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
        $this->jobInstanceRepository->clear();

        /** @var JobInstance $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('tailored_export');
        if (null == $jobInstance) {
            $this->assertEmpty($expectedUuid);

            return;
        }

        $rawParameters = $jobInstance->getRawParameters();

        $sourceUuidInConcatenation = [];
        foreach ($rawParameters['columns'] as $column) {
            $sourceUuidInConcatenation[$column['uuid']] = array_column($column['format']['elements'], 'uuid');
        }

        $this->assertEquals($expectedUuid, $sourceUuidInConcatenation);
    }

    private function createTailoredExportWithoutColumns()
    {
        $this->createTailoredExport(
            '{"filePath":"\/tmp\/export_%job_label%_%datetime%.xlsx","withHeader":true,"linesPerFile":10000,"user_to_notify":null,"is_user_authenticated":false,"with_media":true,"columns":[],"filters":{"data":[{"field":"enabled","operator":"=","value":true},{"field":"categories","operator":"NOT IN","value":[]},{"field":"completeness","operator":"ALL","value":100}]}}'
        );
    }

    private function createTailoredExportWithColumns()
    {
        $this->createTailoredExport(
            '{"filePath":"\/tmp\/export_%job_label%_%datetime%.xlsx","withHeader":true,"linesPerFile":10000,"user_to_notify":null,"is_user_authenticated":false,"with_media":true,"columns":[{"uuid":"sku-column-uuid","target":"sku","sources":[{"uuid":"sku-source-uuid","code":"sku","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"description-column-uuid","target":"description","sources":[{"uuid":"description-de_DE-source-uuid","code":"description","type":"attribute","locale":"de_DE","channel":"ecommerce","operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"weight-column-uuid","target":"weight","sources":[{"uuid":"weight-value-source-uuid","code":"weight","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"value"}},{"uuid":"weight-unit-source-uuid","code":"weight","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"unit_code"}}],"format":{"type":"concat","elements":[]}},{"uuid":"title-column-uuid","target":"concatenation","sources":[{"uuid":"name-source-uuid","code":"name","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"size-source-uuid","code":"size","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"main_color-source-uuid","code":"main_color","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}},{"uuid":"brand-source-uuid","code":"brand","type":"attribute","locale":null,"channel":null,"operations":[],"selection":{"type":"code"}}],"format":{"type":"concat","elements":[]}}],"filters":{"data":[{"field":"enabled","operator":"=","value":true},{"field":"categories","operator":"NOT IN","value":[]},{"field":"completeness","operator":"ALL","value":100}]}}'
        );
    }

    private function createTailoredExport(string $rawParameters)
    {
        $sql = <<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES
            ('tailored_export', 'Tailored Export', 'xlsx_tailored_product_export', 0, 'Akeneo Tailored Export', :raw_parameters, 'export');
        SQL;

        $this->connection->executeStatement($sql, [
            'raw_parameters' => serialize(json_decode($rawParameters, true))
        ]);
    }
}
