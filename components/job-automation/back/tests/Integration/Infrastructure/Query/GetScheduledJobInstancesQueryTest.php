<?php

declare(strict_types=1);

namespace Akeneo\Platform\JobAutomation\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Infrastructure\Query\GetScheduledJobInstancesQuery;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;

class GetScheduledJobInstancesQueryTest extends IntegrationTestCase
{
    private GetScheduledJobInstancesQuery $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get('akeneo.job_automation.query.get_scheduled_job_instances');
        $this->loadFixtures();
    }

    public function test_it_finds_scheduled_job_instances(): void
    {
        $expectedScheduledJobInstances = [
            new ScheduledJobInstance(
                'a_product_import',
                'a_product_import',
                'import',
                ['storage' => ['type' => 'local', 'file_path' => 'test.xlsx']],
                [],
                [],
                true,
                '* * 0 0 0',
                new \DateTimeImmutable('2022-08-10T10:00:00+00:00'),
                null,
                'job_automated_a_product_import',
            ),
            new ScheduledJobInstance(
                'another_product_import',
                'another_product_import',
                'import',
                ['storage' => ['type' => 'local', 'file_path' => 'test.xlsx']],
                [],
                [],
                true,
                '* * 0 0 0',
                new \DateTimeImmutable('2022-08-10T10:00:00+00:00'),
                new \DateTimeImmutable('2022-08-10T10:00:00+00:00'),
                'job_automated_another_product_import',
            )
        ];

        $this->assertEqualsCanonicalizing($expectedScheduledJobInstances, $this->query->all());
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
                'cron_expression' => '* * 0 0 0',
                'last_execution_date' => null,
                'setup_date' => '2022-08-10T10:00:00+00:00',
                'notification_users' => [],
                'notification_user_groups' => [],
            ],
            'raw_parameters' => ['storage' => ['type' => 'local', 'file_path' => 'test.xlsx']]
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
            'scheduled' => true,
            'automation' => [
                'cron_expression' => '* * 0 0 0',
                'last_execution_date' => '2022-08-10T10:00:00+00:00',
                'setup_date' => '2022-08-10T10:00:00+00:00',
                'notification_users' => [],
                'notification_user_groups' => [],
            ],
            'raw_parameters' => ['storage' => ['type' => 'local', 'file_path' => 'test.xlsx']]
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
            'scheduled' => false,
            'automation' => null,
            'raw_parameters' => ['storage' => ['type' => 'local', 'file_path' => 'test.xlsx']]
        ]);
    }
}
