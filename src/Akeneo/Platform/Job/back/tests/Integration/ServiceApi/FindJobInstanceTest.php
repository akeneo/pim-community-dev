<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\ServiceApi;

use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobInstanceTest extends IntegrationTestCase
{
    private FindJobInstanceInterface $findJobInstanceQuery;

    protected function setUp(): void {
        parent::setUp();
        $this->findJobInstanceQuery = $this->get(FindJobInstanceInterface::class);
    }

    /**
     * @test
     */
    public function it_returns_job_instances(): void {
        $this->loadFixtures();

        $query = new JobInstanceQuery();

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
    public function it_returns_job_instances_filtered_on_type(): void {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
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
    public function it_returns_searched_job_instances(): void {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
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
    public function it_returns_paginated_job_instances(): void {
        $this->loadFixtures();

        $query = new JobInstanceQuery();
        $query->pagination->page = 1;
        $query->pagination->limit = 10;

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

    private function loadFixtures(): void {
        $productImportJobInstance = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'label' => 'A product import',
        ]);

        $productExportJobInstance = $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'label' => 'A product export',
        ]);
    }
}
