<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\ServiceAPI\Query\FindJobInstanceCodes;
use Akeneo\Platform\Job\ServiceAPI\Query\JobInstanceQuery;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class SqlFindJobInstanceCodesTest extends IntegrationTestCase
{
    private FindJobInstanceCodes $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get('Akeneo\Platform\Job\ServiceAPI\FindJobInstanceCodes');
        $this->loadFixtures();
    }

    public function test_it_finds_scheduled_job_instance_codes(): void
    {
        $expectedJobInstanceCodes = [
            'another_product_import'
        ];

        $query = new JobInstanceQuery(isScheduled: true);

        $this->assertEqualsCanonicalizing($expectedJobInstanceCodes, $this->query->fromQuery($query));
    }

    public function test_it_finds_not_scheduled_job_instance_codes(): void
    {
        $expectedJobInstanceCodes = [
            'a_product_import',
            'a_product_export'
        ];

        $query = new JobInstanceQuery(isScheduled: false);

        $this->assertEqualsCanonicalizing($expectedJobInstanceCodes, $this->query->fromQuery($query));
    }

    public function test_it_finds_job_instance_codes(): void
    {
        $expectedJobInstanceCodes = [
            'a_product_import',
            'another_product_import',
            'a_product_export'
        ];

        $query = new JobInstanceQuery();

        $this->assertEqualsCanonicalizing($expectedJobInstanceCodes, $this->query->fromQuery($query));
    }

    private function loadFixtures()
    {
        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_import',
            'job_name' => 'a_product_import',
            'label' => 'A product import',
            'type' => 'import',
            'scheduled' => false,
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'another_product_import',
            'job_name' => 'another_product_import',
            'label' => 'Another product import',
            'type' => 'import',
            'scheduled' => true,
        ]);

        $this->fixturesJobHelper->createJobInstance([
            'code' => 'a_product_export',
            'job_name' => 'a_product_export',
            'label' => 'A product export',
            'type' => 'export',
            'scheduled' => false,
        ]);
    }
}
