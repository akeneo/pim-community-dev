<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Test\Acceptance\Application\DeleteJobInstance;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceHandler;
use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\DeleteJobInstanceHandlerInterface;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryDeleteJobInstance;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySecurityFacade;

final class DeleteJobInstanceHandlerTest extends AcceptanceTestCase
{
    private DeleteJobInstanceHandlerInterface $deleteJobInstanceHandler;
    private InMemoryDeleteJobInstance $deleteJobInstance;
    private InMemorySecurityFacade $securityFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteJobInstanceHandler = $this->get(DeleteJobInstanceHandler::class);
        $this->deleteJobInstance = $this->get(DeleteJobInstanceInterface::class);
        $this->securityFacade = $this->get('akeneo.job.security_facade');

        $this->securityFacade->setIsGranted('pim_importexport_export_profile_remove', true);
    }

    /**
     * @test
     */
    public function it_deletes_one_job(): void
    {
        $this->assertContains('job_1', $this->deleteJobInstance->getJobCodes());

        $this->deleteJobInstanceHandler->handle(new DeleteJobInstanceCommand(['job_1']));

        $this->assertNotContains('job_1', $this->deleteJobInstance->getJobCodes());
    }

    /**
     * @test
     */
    public function it_deletes_several_job(): void
    {
        $this->assertContains('job_1', $this->deleteJobInstance->getJobCodes());
        $this->assertContains('job_2', $this->deleteJobInstance->getJobCodes());

        $this->deleteJobInstanceHandler->handle(new DeleteJobInstanceCommand(['job_1', 'job_2']));

        $this->assertNotContains('job_1', $this->deleteJobInstance->getJobCodes());
        $this->assertNotContains('job_2', $this->deleteJobInstance->getJobCodes());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_right_is_not_granted(): void
    {
        $this->securityFacade->setIsGranted('pim_importexport_export_profile_remove', false);

        $this->expectExceptionMessage('Insufficient privilege');

        $this->deleteJobInstanceHandler->handle(new DeleteJobInstanceCommand(['job_1']));
    }
}
