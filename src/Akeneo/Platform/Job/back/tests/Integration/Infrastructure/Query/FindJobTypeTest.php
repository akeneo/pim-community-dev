<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\Job\Application\FindJobType\FindJobTypeInterface;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class FindJobTypeTest extends IntegrationTestCase
{
    private FindJobTypeInterface $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(FindJobTypeInterface::class);
        $this->fixturesLoader->loadFixtures();
    }

    public function test_it_find_job_types(): void
    {
        $expectedJobTypes = [
            'import',
            'export',
        ];

        $this->assertEqualsCanonicalizing($expectedJobTypes, $this->query->visible());
    }
}
