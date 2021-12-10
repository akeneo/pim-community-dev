<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\FindJobTypes;

use Akeneo\Platform\Job\Application\FindJobTypes\FindJobTypesHandler;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryFindJobTypes;

class FindJobTypesHandlerTest extends AcceptanceTestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_returns_job_types()
    {
        $expectedJobTypes = ['export', 'import'];
        $this->getFindJobTypes()->mockFindResult($expectedJobTypes);

        $result = $this->getHandler()->find();

        $this->assertEquals($expectedJobTypes, $result);
    }

    private function getFindJobTypes(): InMemoryFindJobTypes
    {
        return $this->get('Akeneo\Platform\Job\Application\FindJobTypes\FindJobTypesInterface');
    }

    private function getHandler(): FindJobTypesHandler
    {
        return $this->get('Akeneo\Platform\Job\Application\FindJobTypes\FindJobTypesHandler');
    }
}
