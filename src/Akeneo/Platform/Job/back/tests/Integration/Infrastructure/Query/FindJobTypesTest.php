<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\FindJobTypesInterface;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobTypesTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesLoader->loadFixtures();
    }

    public function test_it_find_job_types(): void
    {
        $expectedJobTypes = [
            'import',
            'export',
        ];

        $this->assertEqualsCanonicalizing($expectedJobTypes, $this->getQuery()->visible());
    }

    private function getQuery(): FindJobTypesInterface
    {
        return $this->get('Akeneo\Platform\Job\Application\SearchJobExecution\FindJobTypesInterface');
    }
}
