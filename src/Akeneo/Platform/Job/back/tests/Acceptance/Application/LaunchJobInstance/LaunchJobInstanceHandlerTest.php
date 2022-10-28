<?php

namespace Akeneo\Platform\Job\Test\Acceptance\Application\LaunchJobInstance;

use Akeneo\Platform\Job\Application\CreateJobInstanceHandler;
use Akeneo\Platform\Job\Application\LaunchJobInstance\LaunchJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\File;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryCreateJobExecutionHandler;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaunchJobInstanceHandlerTest extends AcceptanceTestCase
{
    /**
     * @var object|null|mixed
     */
    public $handler;
    private LaunchJobInstanceHandler $launchJobInstanceHandler;
    private InMemoryCreateJobExecutionHandler $createJobExecutionHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->get(LaunchJobInstanceHandler::class);
        $this->createJobExecutionHandler = $this->get(\Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface::class);
    }

    /**
     * @test
     */
    public function it_launches_a_new_job_instance(): void {
        $fileName = 'simple_import.xlsx';
        $file = fopen('php://temp', 'r');

        $launchCommand = new LaunchJobInstanceCommand(
            'xlsx_product_import',
            new File($fileName, $file)
        );

        $result = $this->handler->handle($launchCommand);
        fclose($file);

        $jobExecutionId = $this->createJobExecutionHandler->getLastId();

        $expected = new LaunchJobInstanceResult(
            $jobExecutionId,
            sprintf('/job/show/%d', $jobExecutionId)
        );

        $this->assertEquals($expected, $result);
    }
}
