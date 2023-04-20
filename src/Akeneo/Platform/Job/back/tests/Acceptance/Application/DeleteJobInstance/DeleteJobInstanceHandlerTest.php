<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Test\Acceptance\Application\DeleteJobInstance;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobByCodesInterface;
use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceHandlerInterface;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryDeleteJobByCodes;

final class DeleteJobInstanceHandlerTest extends AcceptanceTestCase
{
    private DeleteJobInstanceHandlerInterface $deleteJobInstanceHandler;

    private InMemoryDeleteJobByCodes $deleteJobByCodes;

    protected function setUp(): void
    {
        $this->deleteJobInstanceHandler = $this->get(DeleteJobInstanceHandler::class);
        $this->deleteJobByCodes = $this->get(DeleteJobByCodesInterface::class);
        DeleteJobInstanceHandlerTest::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_delete_one_job()
    {
        $this->deleteJobByCodes->reset();

        $this->deleteJobInstanceHandler->handle(new DeleteJobInstanceCommand(['job_1']));

        $this->assertNotContains(['code' => 'job_1'], $this->deleteJobByCodes->getJobs());
    }

    /**
     * @test
     */
    public function it_delete_several_job()
    {
        $this->deleteJobByCodes->reset();

        $this->deleteJobInstanceHandler->handle(new DeleteJobInstanceCommand(['job_1', 'job_2']));

        $this->assertNotContains([
            ['code' => 'job_1'],
            ['code' => 'job_2']
        ], $this->deleteJobByCodes->getJobs());
    }
}
