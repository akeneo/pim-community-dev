<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserInterface;
use Akeneo\Platform\Job\Application\SearchJobUser\SearchJobUserQuery;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class SearchJobUserTest extends IntegrationTestCase
{
    private SearchJobUserInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(SearchJobUserInterface::class);

        $jobInstances = [
            'a_product_import' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'a_product_import',
                'job_name' => 'a_product_import',
                'label' => 'a_product_import',
                'type' => 'import',
            ]),
            'a_not_visible_job' => $this->fixturesJobHelper->createJobInstance([
                'code' => 'prepare_evaluation',
                'job_name' => 'prepare_evaluation',
                'label' => 'prepare_evaluation',
                'type' => 'data_quality_insights',
            ]),
        ];

        $this->fixturesJobHelper->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'julia',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'julien',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'admin',
            'job_instance_id' => $jobInstances['a_product_import'],
        ]);
        $this->fixturesJobHelper->createJobExecution([
            'user' => 'bob',
            'job_instance_id' => $jobInstances['a_not_visible_job'],
            'is_visible' => false,
        ]);
    }

    public function test_it_returns_job_users(): void
    {
        $query = new SearchJobUserQuery();

        $expectedJobUsers = [
            'admin',
            'julia',
            'julien',
        ];

        $this->assertEqualsCanonicalizing($expectedJobUsers, $this->query->search($query));
    }

    public function test_it_returns_filtered_job_users_on_username(): void
    {
        $query = new SearchJobUserQuery();
        $query->search = 'juli';

        $expectedJobUsers = ['julia', 'julien'];

        $this->assertEqualsCanonicalizing($expectedJobUsers, $this->query->search($query));
    }
}
