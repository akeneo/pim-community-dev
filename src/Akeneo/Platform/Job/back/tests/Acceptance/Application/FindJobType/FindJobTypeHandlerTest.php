<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\FindJobType;

use Akeneo\Platform\Job\Application\FindJobType\FindJobTypeHandler;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryFindJobType;

class FindJobTypeHandlerTest extends AcceptanceTestCase
{
    private InMemoryFindJobType $findJobType;
    private FindJobTypeHandler $handler;

    protected function setUp(): void
    {
        $this->findJobType = $this->get('Akeneo\Platform\Job\Application\FindJobType\FindJobTypeInterface');
        $this->handler = $this->get('Akeneo\Platform\Job\Application\FindJobType\FindJobTypeHandler');
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_job_types()
    {
        $expectedJobTypes = ['export', 'import'];
        $this->findJobType->mockFindResult($expectedJobTypes);

        $result = $this->handler->find();

        $this->assertEquals($expectedJobTypes, $result);
    }
}
