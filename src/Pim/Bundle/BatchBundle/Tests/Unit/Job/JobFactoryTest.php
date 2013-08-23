<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Job\JobFactory;

/**
 * Tests related to the JobFactory class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateJob()
    {
        $logger = new Logger('JobLogger');
        $logger->pushHandler(new TestHandler());

        $jobRepository = $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');
        $eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');

        $jobFactory = new JobFactory($eventDispatcher, $jobRepository);
        $job = $jobFactory->createJob('my_test_job');

        $this->assertInstanceOf(
            'Pim\\Bundle\\BatchBundle\\Job\\JobInterface',
            $job
        );
    }
}
