<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Step;

use Pim\Bundle\BatchBundle\Step\StepFactory;

/**
 * Tests related to the JobFactory class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateStep()
    {
        $eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $jobRepository   = $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $jobFactory = new StepFactory($eventDispatcher, $jobRepository);

        $reader = $this
            ->getMockBuilder('Pim\\Bundle\\BatchBundle\\Item\\ItemReaderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $processor = $this
            ->getMockBuilder('Pim\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $writer = $this
            ->getMockBuilder('Pim\\Bundle\\BatchBundle\\Item\\ItemWriterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $step = $jobFactory->createStep('my_test_job', $reader, $processor, $writer);

        $this->assertInstanceOf('Pim\\Bundle\\BatchBundle\\Step\\StepInterface', $step);
        $this->assertAttributeEquals($reader, 'reader', $step);
        $this->assertAttributeEquals($processor, 'processor', $step);
        $this->assertAttributeEquals($writer, 'writer', $step);
        $this->assertAttributeEquals($eventDispatcher, 'eventDispatcher', $step);
        $this->assertAttributeEquals($jobRepository, 'jobRepository', $step);
    }
}
