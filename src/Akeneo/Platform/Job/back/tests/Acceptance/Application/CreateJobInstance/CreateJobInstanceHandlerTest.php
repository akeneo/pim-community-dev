<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application\CreateJobInstance;

use Akeneo\Platform\Job\Application\CreateJobInstance\CreateJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CannotCreateJobInstanceException;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryJobInstanceSaver;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemorySecurityFacade;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class CreateJobInstanceHandlerTest extends AcceptanceTestCase
{
    private CreateJobInstanceHandler $handler;
    private InMemoryJobInstanceSaver $saver;
    private InMemorySecurityFacade $securityFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(CreateJobInstanceHandler::class);
        $this->saver = $this->get('akeneo_platform.saver.job_instance');
        $this->securityFacade = $this->get('akeneo.job.security_facade');

        $this->securityFacade->setIsGranted('pim_importexport_export_profile_create', true);
        $this->securityFacade->setIsGranted('pim_importexport_import_profile_create', true);
    }

    /**
     * @test
     */
    public function it_creates_a_job_instance(): void
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
        );
        $this->handler->handle($command);

        $this->assertInstanceOf(JobInstance::class, $this->saver->get('test_job'));
    }

    /**
     * @test
     */
    public function a_job_instance_is_visible_by_default(): void
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'visible_job',
            'visible_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
        );
        $this->handler->handle($command);

        $job = $this->saver->get('visible_job');

        $this->assertTrue($job->isVisible());
    }

    /**
     * @test
     */
    public function a_job_instance_can_be_invisible(): void
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'invisible_job',
            'invisible_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
            false,
        );
        $this->handler->handle($command);

        $job = $this->saver->get('invisible_job');

        $this->assertFalse($job->isVisible());
    }

    /**
     * @test
     */
    public function it_throws_business_exception_when_job_name_does_not_exist(): void
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'foo',
            [],
        );

        $this->expectException(CannotCreateJobInstanceException::class);
        $this->expectExceptionMessage('Job "foo" does not exist');

        $this->handler->handle($command);
    }

    /**
     * @test
     */
    public function it_throws_business_exception_when_user_does_not_have_privilege_to_create_export(): void
    {
        $this->securityFacade->setIsGranted('pim_importexport_export_profile_create', false);

        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
        );

        $this->expectException(CannotCreateJobInstanceException::class);
        $this->expectExceptionMessage('Insufficient privilege');

        $this->handler->handle($command);
    }

    /**
     * @test
     */
    public function it_throws_business_exception_when_user_does_not_have_privilege_to_create_import(): void
    {
        $this->securityFacade->setIsGranted('pim_importexport_import_profile_create', false);

        $command = new CreateJobInstanceCommand(
            'import',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
        );

        $this->expectException(CannotCreateJobInstanceException::class);
        $this->expectExceptionMessage('Insufficient privilege');

        $this->handler->handle($command);
    }

    /**
     * @test
     */
    public function it_throws_invalid_job_exception_when_job_parameters_is_not_valid()
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'csv_product_import',
            ['undefined_param' => 'should_fail'],
        );

        $this->expectException(InvalidJobException::class);

        $this->handler->handle($command);
    }

    /**
     * @test
     */
    public function it_throws_invalid_job_exception_when_job_instance_is_not_valid()
    {
        $label = str_repeat('a', 260);

        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            $label,
            'Akeneo CSV Connector',
            'csv_product_import',
            [],
        );

        $this->expectException(InvalidJobException::class);

        $this->handler->handle($command);
    }
}
