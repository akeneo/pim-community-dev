<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

class ProcessorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorRegistry
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = new ProcessorRegistry();
    }

    public function testRegisterProcessor()
    {
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $entityName, $alias);
        $this->assertAttributeEquals(
            array($entityName => array($alias => $processor)),
            'processors',
            $this->registry
        );

        return $this->registry;
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Processor "processor_alias" for entity "entity_name" already exist
     */
    public function testRegisterProcessorFails()
    {
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $entityName, $alias);
        $this->registry->registerProcessor($processor, $entityName, $alias);
    }

    public function testUnregisterProcessor()
    {
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $entityName, $alias);
        $this->registry->unregisterProcessor($entityName, $alias);
        $this->assertAttributeEquals(
            array($entityName => array()),
            'processors',
            $this->registry
        );
    }

    public function testHasProcessor()
    {
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->assertFalse($this->registry->hasProcessor($entityName, $alias));
        $this->registry->registerProcessor($processor, $entityName, $alias);
        $this->assertTrue($this->registry->hasProcessor($entityName, $alias));
    }

    public function testGetProcessor()
    {
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $entityName, $alias);
        $this->assertSame($processor, $this->registry->getProcessor($entityName, $alias));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException
     * @expectedExceptionMessage Processor "processor_alias" for entity "entity_name" is not exist
     */
    public function testGetProcessorFails()
    {
        $this->registry->getProcessor('entity_name', 'processor_alias');
    }

    public function testRegisterProcessors()
    {
        $entityName = 'entity_name';
        $fooAlias = 'foo_alias';
        $fooProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');
        $barAlias = 'bar_alias';
        $barProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($fooProcessor, $entityName, $fooAlias);
        $this->registry->registerProcessor($barProcessor, $entityName, $barAlias);

        $this->assertEquals(
            array($fooAlias => $fooProcessor, $barAlias => $barProcessor),
            $this->registry->getProcessorsByEntity($entityName)
        );
    }
}
