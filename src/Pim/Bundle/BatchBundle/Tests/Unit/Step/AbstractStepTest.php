<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BatchBundle\Job\Job;
use Pim\Bundle\BatchBundle\Entity\Job as JobInstance;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\BatchBundle\Job\SimpleStepHandler;
use Pim\Bundle\BatchBundle\Tests\Unit\Step\InterruptedStep;
use Pim\Bundle\BatchBundle\Tests\Unit\Step\IncompleteStep;
use Pim\Bundle\BatchBundle\Tests\Unit\Job\MockJobRepository;

/**
 * Tests related to the AbstractStep class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AbstractStepTest extends \PHPUnit_Framework_TestCase
{
    const STEP_TEST_NAME = 'step_test';

    protected $step          = null;
    protected $logger        = null;
    protected $jobRepository = null;

    protected function setUp()
    {
        $this->logger = new Logger('JobLogger');
        $this->logger->pushHandler(new TestHandler());

        $this->jobRepository = new MockJobRepository();

        $this->step = $this->getMockForAbstractClass(
            'Pim\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array(self::STEP_TEST_NAME)
        );

        $this->step->setLogger($this->logger);
        $this->step->setJobRepository($this->jobRepository);
    }

    public function testGetSetJobRepository()
    {
        $step = $this->getMockForAbstractClass(
            'Pim\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array(self::STEP_TEST_NAME)
        );

        $this->assertNull($step->getJobRepository());
        $this->assertEntity($step->setJobRepository($this->jobRepository));
        $this->assertSame($this->jobRepository, $step->getJobRepository());
    }


    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Step\AbstractStep', $entity);
    }
}
