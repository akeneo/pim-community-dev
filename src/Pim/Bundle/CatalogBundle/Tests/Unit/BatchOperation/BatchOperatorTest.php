<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\BatchOperation;

use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperator;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchOperatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->operator = new BatchOperator($this->getFlexibleManagerMock());
    }

    public function testRegisterBatchOperation()
    {
        $operation = $this->getBatchOperationMock();
        $this->operator->registerBatchOperation('foo', $operation);

        $this->assertAttributeEquals(array('foo' => $operation), 'operations', $this->operator);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Operation "foo" is already registered
     */
    public function testPreventRegisteringTwoOperationsWithTheSameAlias()
    {
        $this->operator->registerBatchOperation('foo', $this->getBatchOperationMock());
        $this->operator->registerBatchOperation('foo', $this->getBatchOperationMock());
    }

    public function testGetOperationChoices()
    {
        $this->operator->registerBatchOperation('foo', $this->getBatchOperationMock());
        $this->operator->registerBatchOperation('bar', $this->getBatchOperationMock());

        $this->assertEquals(
            array(
                'foo' => 'pim_catalog.batch_operation.foo.label',
                'bar' => 'pim_catalog.batch_operation.bar.label',
            ),
            $this->operator->getOperationChoices()
        );
    }

    public function testSetOperationAlias()
    {
        $operation = $this->getBatchOperationMock();
        $this->operator->registerBatchOperation('foo', $operation);

        $this->operator->setOperationAlias('foo');

        $this->assertEquals('foo', $this->operator->getOperationAlias());
        $this->assertEquals($operation, $this->operator->getOperation());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Operation "foo" is not registered
     */
    public function testSetUnknownOperationAlias()
    {
        $this->operator->setOperationAlias('foo');
    }

    public function testPerformOperation()
    {
        $operation = $this->getBatchOperationMock();
        $this->operator->registerBatchOperation('foo', $operation);
        $this->operator->setOperationAlias('foo');

        $operation->expects($this->once())
            ->method('perform')
            ->with(array(1, 2, 3));

        $this->operator->performOperation(array('products' => array(1, 2, 3)));
    }

    public function testInitializeOperation()
    {
        $operation = $this->getBatchOperationMock();
        $this->operator->registerBatchOperation('foo', $operation);
        $this->operator->setOperationAlias('foo');

        $operation->expects($this->once())
            ->method('initialize')
            ->with(array(1, 2, 3));

        $this->operator->initializeOperation(array('products' => array(1, 2, 3)));
    }

    protected function getFlexibleManagerMock()
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('findByIds')
            ->will($this->returnArgument(0));

        return $manager;
    }

    protected function getBatchOperationMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation');
    }
}
