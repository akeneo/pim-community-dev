<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\CreateEntity;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;

class CreateEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    /**
     * @var PostActionInterface
     */
    protected $postAction;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\ContextAccessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->postAction = new CreateEntity($this->contextAccessor);
    }

    /**
     * @param mixed $options
     * @dataProvider invalidOptionsDataProvider
     * @expectedException Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Class name and property parameters are required
     */
    public function testInitializeException($options)
    {
        $this->postAction->initialize($options);
    }

    public function invalidOptionsDataProvider()
    {
        return array(
            array(array()),
            array(array(1))
        );
    }

    /**
     * @param mixed $options
     * @dataProvider validOptionsDataProvider
     */
    public function testInitialize($options)
    {
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface',
            $this->postAction->initialize($options)
        );
    }

    public function validOptionsDataProvider()
    {
        return array(
            array(array('stdClass', 'test'))
        );
    }

    public function testExecute()
    {
        $context = array();
        $this->contextAccessor->expects($this->once())
            ->method('setValue')
            ->with($context, 'test', $this->isInstanceOf('stdClass'));
        $this->postAction->initialize(array('stdClass', 'test'));
        $this->postAction->execute($context);
    }
}
