<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Platform\JobAutomation\Infrastructure\Query\UpdateJobInstanceAutomationLastExecutionDateQuery;
use Doctrine\DBAL\Connection;

class UpdateJobInstanceAutomationLastExecutionDateQueryTest extends IntegrationTestCase
{
    private UpdateJobInstanceAutomationLastExecutionDateQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get('akeneo.job_automation.query.update_job_instance_automation_last_execution_date');
        $this->loadFixtures();
    }

    public function test_it_updates_last_execution_dates_for_job_instances_codes(): void
    {
        $expectedLastExecutionDate = '2022-08-12T12:00:00+00:00';
        $lastExecutionDate = new \DateTimeImmutable($expectedLastExecutionDate);

        $this->query->forJobInstanceCode('a_product_import', $lastExecutionDate);
        $this->query->forJobInstanceCode('another_product_import', $lastExecutionDate);
        $updatedJobInstanceAutomationLastExecutionDates = $this->getAutomationLastExecutionDateForJobInstanceCodes(['a_product_import', 'another_product_import']);

        foreach ($updatedJobInstanceAutomationLastExecutionDates as $actualLastExecutionDate) {
            $this->assertEquals($expectedLastExecutionDate, $actualLastExecutionDate);
        }
    }

    private function loadFixtures()
    {
        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
            'scheduled' => true,
            'automation' => [
                'cron_expression' => '* * * * *',
                'last_execution_date' => null,
                'setup_date' => '2022-08-10T10:00:00+00:00',
            ],
            'raw_parameters' => ['storage']
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
            'scheduled' => true,
            'automation' => [
                'cron_expression' => '* * * * *',
                'last_execution_date' => '2022-08-10T10:00:00+00:00',
                'setup_date' => '2022-08-10T10:00:00+00:00'
            ],
            'raw_parameters' => ['storage']
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
            'scheduled' => false,
            'automation' => null,
            'raw_parameters' => ['storage']
        ]);
    }

    private function getAutomationLastExecutionDateForJobInstanceCodes(array $jobInstanceCodes): array
    {
        $sql = <<<SQL
    SELECT automation FROM akeneo_batch_job_instance WHERE code IN (:jobInstanceCodes);
SQL;

        $rawAutomations = $this->get('database_connection')
            ->executeQuery($sql, ['jobInstanceCodes' => $jobInstanceCodes], ['jobInstanceCodes' => Connection::PARAM_STR_ARRAY])
            ->fetchFirstColumn();

        return array_map(static fn (string $automation) => json_decode($automation, true)['last_execution_date'], $rawAutomations);
    }
}
