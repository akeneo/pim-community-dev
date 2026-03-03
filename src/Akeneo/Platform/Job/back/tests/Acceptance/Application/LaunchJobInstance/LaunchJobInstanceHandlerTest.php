<?php

namespace Akeneo\Platform\Job\Test\Acceptance\Application\LaunchJobInstance;

use Akeneo\Platform\Job\Application\LaunchJobInstance\LaunchJobInstanceHandler;
use Akeneo\Platform\Job\ServiceApi\JobInstance\File;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Platform\Job\Test\Acceptance\AcceptanceTestCase;
use Akeneo\Platform\Job\Test\Acceptance\FakeServices\InMemoryPublishJobToQueue;

class LaunchJobInstanceHandlerTest extends AcceptanceTestCase
{
    public LaunchJobInstanceHandler $handler;
    private InMemoryPublishJobToQueue $publishJobToQueue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->get(LaunchJobInstanceHandler::class);
        $this->publishJobToQueue = $this->get('akeneo_batch_queue.queue.publish_job_to_queue');
    }

    /**
     * @test
     */
    public function it_launches_a_new_job_instance(): void
    {
        $fileName = 'simple_import.xlsx';
        $file = fopen('php://temp', 'r');

        $launchCommand = new LaunchJobInstanceCommand(
            'xlsx_product_import',
            new File($fileName, $file)
        );

        $result = $this->handler->handle($launchCommand);
        fclose($file);

        $jobExecutionId = $this->publishJobToQueue->getLastId();

        $expected = new LaunchJobInstanceResult(
            $jobExecutionId,
            sprintf('/job/show/%d', $jobExecutionId)
        );

        $this->assertEquals($expected, $result);
    }
}
