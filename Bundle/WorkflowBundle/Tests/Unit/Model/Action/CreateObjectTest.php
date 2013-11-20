<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\Action\CreateObject;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class CreateObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreateObject
     */
    protected $action;

    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();
        $this->action = new CreateObject($this->contextAccessor);
    }

    protected function tearDown()
    {
        unset($this->contextAccessor);
        unset($this->action);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Class name parameter is required
     */
    public function testInitializeExceptionNoClassName()
    {
        $this->action->initialize(array('some' => 1, 'attribute' => $this->getPropertyPath()));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute name parameter is required
     */
    public function testInitializeExceptionNoAttribute()
    {
        $this->action->initialize(array('class' => 'stdClass'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute must be valid property definition.
     */
    public function testInitializeExceptionInvalidAttribute()
    {
        $this->action->initialize(array('class' => 'stdClass', 'attribute' => 'string'));
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Object data must be an array.
     */
    public function testInitializeExceptionInvalidData()
    {
        $this->action->initialize(
            array('class' => 'stdClass', 'attribute' => $this->getPropertyPath(), 'data' => 'string_value')
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Object constructor arguments must be an array.
     */
    public function testInitializeExceptionInvalidArguments()
    {
        $this->action->initialize(
            array('class' => 'stdClass', 'attribute' => $this->getPropertyPath(), 'arguments' => 'string_value')
        );
    }

    public function testInitialize()
    {
        $options = array('class' => 'stdClass', 'attribute' => $this->getPropertyPath());
        $this->assertEquals($this->action, $this->action->initialize($options));
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    /**
     * @param array $options
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $options)
    {
        $context = new ItemStub(array());
        $attributeName = (string)$options['attribute'];
        $this->action->initialize($options);
        $this->action->execute($context);
        $this->assertNotNull($context->$attributeName);
        $this->assertInstanceOf($options['class'], $context->$attributeName);

        if ($context->$attributeName instanceof ItemStub) {
            /** @var ItemStub $entity */
            $entity = $context->$attributeName;
            $expectedData = !empty($options['data']) ? $options['data'] : array();
            $this->assertInstanceOf($options['class'], $entity);
            $this->assertEquals($expectedData, $entity->getData());
        }
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            'without data' => array(
                'options' => array(
                    'class'     => 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub',
                    'attribute' => new PropertyPath('test_attribute'),
                )
            ),
            'with data' => array(
                'options' => array(
                    'class'     => 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub',
                    'attribute' => new PropertyPath('test_attribute'),
                    'data'      => array('key1' => 'value1', 'key2' => 'value2'),
                )
            ),
            'with arguments' => array(
                'options' => array(
                    'class'     => '\DateTime',
                    'attribute' => new PropertyPath('test_attribute'),
                    'arguments' => array('now'),
                )
            )
        );
    }

    protected function getPropertyPath()
    {
        return $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyPath')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
