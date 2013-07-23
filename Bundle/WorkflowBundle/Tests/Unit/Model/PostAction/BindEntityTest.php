<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\PostAction\BindEntity;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class BindEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityBinderMock()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->setMethods(array('bind'))
            ->getMock();
    }

    public function testInitialize()
    {
        $options = array(
            'attribute' => new PropertyPath('data.value'),
            'step'      => 'some_step',
        );
        $postAction = new BindEntity(new ContextAccessor(), $this->getEntityBinderMock());
        $this->assertEquals($postAction, $postAction->initialize($options));
        $this->assertAttributeEquals($options, 'options', $postAction);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute name parameter is required
     */
    public function testInitializeNoAttributeException()
    {
        $options = array();
        $postAction = new BindEntity(new ContextAccessor(), $this->getEntityBinderMock());
        $postAction->initialize($options);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     * @expectedExceptionMessage Attribute name parameter must be instance of PropertyPath
     */
    public function testInitializeAttributeNotPropertyPathException()
    {
        $options = array('attribute' => 'string_value');
        $postAction = new BindEntity(new ContextAccessor(), $this->getEntityBinderMock());
        $postAction->initialize($options);
    }

    /**
     * @param array $options
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $options)
    {
        $entity = new \DateTime('now');
        $workflowData = new WorkflowData();
        $workflowData->set('entity', $entity);
        $workflowItem = new WorkflowItem();
        $workflowItem->setData($workflowData);

        $expectedStep = !empty($options['step']) ? $options['step'] : null;

        $entityBinder = $this->getEntityBinderMock();
        $entityBinder->expects($this->once())
            ->method('bind')
            ->with($workflowItem, $entity, $expectedStep);

        $postAction = new BindEntity(new ContextAccessor(), $entityBinder);
        $postAction->initialize($options);
        $postAction->execute($workflowItem);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return array(
            'without step' => array(
                'options' => array(
                    'attribute' => new PropertyPath('data.entity'),
                )
            ),
            'with step' => array(
                'options' => array(
                    'attribute' => new PropertyPath('data.entity'),
                    'step' => 'custom_step'
                )
            ),
        );
    }
}
