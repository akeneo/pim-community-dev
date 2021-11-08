<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobTypesTest extends IntegrationTestCase
{
    private array $fixtures = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->fixturesLoader->loadProductImportExportFixtures();
    }

    public function test_it_find_job_types(): void
    {
        $findJobTypesQuery = $this->get('Akeneo\Platform\Job\Domain\Query\FindJobTypesInterface');

        $expectedJobTypes = [
            'import',
            'export'
        ];

        $this->assertEqualsCanonicalizing($expectedJobTypes, $findJobTypesQuery->visible());
    }
}
