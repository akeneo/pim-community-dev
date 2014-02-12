<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Akeneo\Bundle\BatchBundle\Job\JobFactory;

/**
 * Tests related to the JobFactory class
 *
 */
class JobFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateJob()
    {
        $logger = new Logger('JobLogger');
        $logger->pushHandler(new TestHandler());

        $jobRepository = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');
        $eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');

        $jobFactory = new JobFactory($eventDispatcher, $jobRepository);
        $job = $jobFactory->createJob('my_test_job');

        $this->assertInstanceOf(
            'Akeneo\\Bundle\\BatchBundle\\Job\\JobInterface',
            $job
        );
    }
}
