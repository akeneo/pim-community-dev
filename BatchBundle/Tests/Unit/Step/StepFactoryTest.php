<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step;

use Akeneo\Bundle\BatchBundle\Step\StepFactory;

/**
 * Tests related to the JobFactory class
 *
 */
class StepFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateStep()
    {
        $eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $jobRepository   = $this->getMock('Akeneo\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $stepFactory = new StepFactory($eventDispatcher, $jobRepository);

        $reader = $this
            ->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Item\\ItemReaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $processor = $this
            ->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $writer = $this
            ->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Item\\ItemWriterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $services = array ('reader' => $reader, 'processor' => $processor, 'writer' => $writer);
        $class = 'Akeneo\Bundle\BatchBundle\Step\ItemStep';
        $step = $stepFactory->createStep('my_test_job', $class, $services, array());

        $this->assertInstanceOf('Akeneo\\Bundle\\BatchBundle\\Step\\StepInterface', $step);
        $this->assertAttributeEquals($reader, 'reader', $step);
        $this->assertAttributeEquals($processor, 'processor', $step);
        $this->assertAttributeEquals($writer, 'writer', $step);
        $this->assertAttributeEquals($eventDispatcher, 'eventDispatcher', $step);
        $this->assertAttributeEquals($jobRepository, 'jobRepository', $step);
    }
}
