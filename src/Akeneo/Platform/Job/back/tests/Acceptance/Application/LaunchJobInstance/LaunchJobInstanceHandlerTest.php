<?php

namespace Akeneo\Platform\Job\Test\Acceptance\Application\LaunchJobInstance;

use Akeneo\Platform\Job\Application\LaunchJobInstance\LaunchJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\File;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;

class LaunchJobInstanceHandlerTest extends AcceptanceTestCase
{
    public $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->get(LaunchJobInstanceHandler::class);
        $this->jobRepository = $this->get('akeneo_batch.job_repository');
    }

    /**
     * @test
     */
    public function it_launches_a_new_job_instance(): void
    {
        var_dump('MICHEL');
        $fileName = 'simple_import.xlsx';
        $file = fopen('php://temp', 'r');

        $launchCommand = new LaunchJobInstanceCommand(
            'xlsx_product_import',
            new File($fileName, $file)
        );

        $result = $this->handler->handle($launchCommand);
        fclose($file);

        $jobExecutionId = $this->jobRepository->getLastJobExecution();

        $expected = new LaunchJobInstanceResult(
            $jobExecutionId->getId(),
            sprintf('/job/show/%d', $jobExecutionId->getId())
        );

        $this->assertEquals($expected, $result);
    }
}
