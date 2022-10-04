<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\Application;

use Akeneo\Platform\Job\Application\CreateJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryJobInstanceSaver;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;

class CreateJobInstanceHandlerTest extends AcceptanceTestCase
{
    private CreateJobInstanceHandler $handler;
    private InMemoryJobInstanceSaver $saver;

    protected function setUp(): void
    {
        $this->handler = $this->get(CreateJobInstanceHandler::class);
        $this->saver = $this->get('akeneo_platform.saver.job_instance');
        static::bootKernel(['debug' => false]);
    }

    /**
     * @test
     */
    public function it_creates_a_job_instance()
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

        $this->saver->get('test_job');
    }

    /**
     * @test
     */
    public function it_throws_runtime_exception_when_job_name_does_not_exist()
    {
        $command = new CreateJobInstanceCommand(
            'export',
            'test_job',
            'test_job',
            'Akeneo CSV Connector',
            'foo',
            [],
        );

        $this->expectException(\RuntimeException::class);

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
