<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;

class ContextRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextRegistry
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = new ContextRegistry();
    }

    public function testGetByStepExecution()
    {
        $fooStepExecution = $this->createStepExecution();
        $fooContext = $this->registry->getByStepExecution($fooStepExecution);
        $this->assertInstanceOf('Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext', $fooContext);
        $this->assertAttributeEquals($fooStepExecution, 'stepExecution', $fooContext);
        $this->assertSame($fooContext, $this->registry->getByStepExecution($fooStepExecution));

        $barStepExecution = $this->createStepExecution();
        $barContext = $this->registry->getByStepExecution($barStepExecution);
        $this->assertNotSame($barContext, $fooContext);
    }

    protected function createStepExecution()
    {
        return $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
