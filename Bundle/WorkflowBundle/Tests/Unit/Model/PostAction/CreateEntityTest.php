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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\ContextAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->postAction = new CreateEntity($this->contextAccessor, $this->registry);
    }

    /**
     * @expectedException Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Class name parameter is required
     */
    public function testInitializeExceptionNoClassName()
    {
        $this->postAction->initialize(array('some' => 1, 'attribute' => $this->getPropertyPath()));
    }

    /**
     * @expectedException Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute name parameter is required
     */
    public function testInitializeExceptionNoAttribute()
    {
        $this->postAction->initialize(array('class' => 'stdClass', 'some' => $this->getPropertyPath()));
    }

    /**
     * @expectedException Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute must be valid property definition.
     */
    public function testInitializeExceptionInvalidAttribute()
    {
        $this->postAction->initialize(array('class' => 'stdClass', 'attribute' => 'string'));
    }

    public function testInitialize()
    {
        $options = array('class' => 'stdClass', 'attribute' => $this->getPropertyPath());
        $this->assertInstanceOf(
            'Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface',
            $this->postAction->initialize($options)
        );
        $this->assertAttributeEquals($options, 'options', $this->postAction);
    }

    public function testExecute()
    {
        $context = array();
        $options = array('class' => 'stdClass', 'attribute' => $this->getPropertyPath());
        $this->contextAccessor->expects($this->once())
            ->method('setValue')
            ->with($context, $options['attribute'], $this->isInstanceOf('stdClass'));

        $em = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('stdClass'));
        $em->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf('stdClass'));

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->will($this->returnValue($em));

        $this->postAction->initialize($options);
        $this->postAction->execute($context);
    }

    /**
     * @expectedException Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException
     * @expectedExceptionMessage Entity class "stdClass" is not manageable.
     */
    public function testExecuteException()
    {
        $options = array('class' => 'stdClass', 'attribute' => $this->getPropertyPath());
        $context = array();
        $this->contextAccessor->expects($this->never())
            ->method('setValue');
        $this->postAction->initialize($options);
        $this->postAction->execute($context);
    }

    protected function getPropertyPath()
    {
        return $this->getMockBuilder('Symfony\Component\PropertyAccess\PropertyPath')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
