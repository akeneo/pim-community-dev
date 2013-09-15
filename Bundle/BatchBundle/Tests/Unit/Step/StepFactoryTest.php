<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Step;

use Oro\Bundle\BatchBundle\Step\StepFactory;

/**
 * Tests related to the JobFactory class
 *
 */
class StepFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateStep()
    {
        $eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $jobRepository   = $this->getMock('Oro\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $jobFactory = new StepFactory($eventDispatcher, $jobRepository);

        $reader = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Item\\ItemReaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $processor = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $writer = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Item\\ItemWriterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $step = $jobFactory->createStep('my_test_job', $reader, $processor, $writer);

        $this->assertInstanceOf('Oro\\Bundle\\BatchBundle\\Step\\StepInterface', $step);
        $this->assertAttributeEquals($reader, 'reader', $step);
        $this->assertAttributeEquals($processor, 'processor', $step);
        $this->assertAttributeEquals($writer, 'writer', $step);
        $this->assertAttributeEquals($eventDispatcher, 'eventDispatcher', $step);
        $this->assertAttributeEquals($jobRepository, 'jobRepository', $step);
    }
}
