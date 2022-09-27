<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\ServiceApi;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQueryPagination;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobInstanceTest extends IntegrationTestCase
{
    private FindJobInstanceInterface $findJobInstanceQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findJobInstanceQuery = $this->get(FindJobInstanceInterface::class);
    }

    /**
     * @test
     */
    public function it_returns_job_instances(): void
    {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
        $queryPagination = new JobInstanceQueryPagination();

        $query->pagination = $queryPagination;

        $expectedJobInstances = [
            [
                'code' => 'a_product_import',
                'label' => 'A product import',
            ],
            [
                'code' => 'a_product_export',
                'label' => 'A product export',
            ]
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_job_instances_filtered_on_type(): void
    {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
        $queryPagination = new JobInstanceQueryPagination();

        $query->pagination = $queryPagination;
        $query->types = ['export'];

        $expectedJobInstances = [
            [
                'code' => 'a_product_export',
                'label' => 'A product export',
            ]
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_searched_job_instances(): void
    {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
        $queryPagination = new JobInstanceQueryPagination();

        $query->pagination = $queryPagination;
        $query->search = 'a_product_import';

        $expectedJobInstances = [
            [
                'code' => 'a_product_import',
                'label' => 'A product import',
            ]
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    /**
     * @test
     */
    public function it_returns_paginated_job_instances(): void
    {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
        $queryPagination = new JobInstanceQueryPagination();

        $queryPagination->limit = 2;
        $query->pagination = $queryPagination;

        $expectedJobInstances = [
            [
                'code' => 'a_product_import',
                'label' => 'A product import',
            ],
            [
                'code' => 'another_product_import',
                'label' => 'Another product import',
            ]
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));

        $queryPagination->page = 2;
        $queryPagination->limit = 2;
        $query->pagination = $queryPagination;

        $expectedJobInstances = [
            [
                'code' => 'a_scheduled_job',
                'label' => 'A scheduled job',
            ],
            [
                'code' => 'a_quick_export',
                'label' => 'A quick export',
            ]
        ];

        $this->assertEquals($expectedJobInstances, $this->findJobInstanceQuery->fromQuery($query));
    }

    private function loadFixtures(): void
    {
        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_export',
            'job_name' => 'another_product_export',
            'label' => 'Another product export',
            'type' => 'export',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_scheduled_job',
            'job_name' => 'a_scheduled_job',
            'label' => 'A scheduled job',
            'type' => 'scheduled_job',
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_quick_export',
            'job_name' => 'a_quick_export',
            'label' => 'A quick export',
            'type' => 'quick_export',
        ]);
    }
}
