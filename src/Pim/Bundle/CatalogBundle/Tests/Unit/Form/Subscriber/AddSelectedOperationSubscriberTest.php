<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Form\Subscriber\BatchProduct\AddSelectedOperationSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSelectedOperationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->subscriber = new AddSelectedOperationSubscriber();
    }

    public function testInstanceOfEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    public function testPreSetDataWithOperation()
    {
        $form      = $this->getFormMock();
        $operation = $this->getBatchOperationMock('foo_type', array('foo' => 'bar'));
        $data      = $this->getBatchOperatorMock($operation);

        $event = $this->getFormEventMock($form, $data);

        $form->expects($this->once())
            ->method('remove')
            ->with('operationAlias')
            ->will($this->returnSelf());

        $form->expects($this->once())
            ->method('add')
            ->with('operation', 'foo_type', array('foo' => 'bar'));


        $this->subscriber->preSetData($event);
    }

    public function testPreSetDataWithoutOperation()
    {
        $form = $this->getFormMock();
        $data = $this->getBatchOperatorMock(null);

        $event = $this->getFormEventMock($form, $data);

        $form->expects($this->never())
            ->method('remove')
            ->will($this->returnSelf());

        $form->expects($this->never())
            ->method('add');


        $this->subscriber->preSetData($event);
    }

    public function testPreSetDataWithoutData()
    {
        $form = $this->getFormMock();

        $event = $this->getFormEventMock($form, null);

        $form->expects($this->never())
            ->method('remove')
            ->will($this->returnSelf());

        $form->expects($this->never())
            ->method('add');


        $this->subscriber->preSetData($event);
    }

    protected function getFormEventMock($form, $data)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $event->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        return $event;
    }

    protected function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getBatchOperatorMock($operation)
    {
        $operator = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\BatchOperation\BatchOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $operator->expects($this->any())
            ->method('getOperation')
            ->will($this->returnValue($operation));

        return $operator;
    }

    protected function getBatchOperationMock($formType, array $formOptions)
    {
        $operation = $this->getMock('Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation');

        $operation->expects($this->any())
            ->method('getFormType')
            ->will($this->returnValue($formType));

        $operation->expects($this->any())
            ->method('getFormOptions')
            ->will($this->returnValue($formOptions));

        return $operation;
    }
}
