<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Form\Subscriber\MassEditAction;

use Pim\Bundle\EnrichBundle\Form\Subscriber\MassEditAction\AddSelectedOperationSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddSelectedOperationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->subscriber = new AddSelectedOperationSubscriber();
    }

    /**
     * Test related method
     */
    public function testInstanceOfEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    /**
     * Test related method
     */
    public function testPostSetDataWithOperation()
    {
        $form      = $this->getFormMock();
        $operation = $this->getMassEditActionMock('foo_type', array('foo' => 'bar'));
        $data      = $this->getMassEditActionOperatorMock($operation);

        $event = $this->getFormEventMock($form, $data);

        $form->expects($this->once())
            ->method('remove')
            ->with('operationAlias')
            ->will($this->returnSelf());

        $form->expects($this->once())
            ->method('add')
            ->with('operation', 'foo_type', array('foo' => 'bar'));

        $this->subscriber->postSetData($event);
    }

    /**
     * Test related method
     */
    public function testPostSetDataWithoutOperation()
    {
        $form = $this->getFormMock();
        $data = $this->getMassEditActionOperatorMock(null);

        $event = $this->getFormEventMock($form, $data);

        $form->expects($this->never())
            ->method('remove')
            ->will($this->returnSelf());

        $form->expects($this->never())
            ->method('add');

        $this->subscriber->postSetData($event);
    }

    /**
     * Test related method
     */
    public function testPostSetDataWithoutData()
    {
        $form = $this->getFormMock();

        $event = $this->getFormEventMock($form, null);

        $form->expects($this->never())
            ->method('remove')
            ->will($this->returnSelf());

        $form->expects($this->never())
            ->method('add');

        $this->subscriber->postSetData($event);
    }

    /**
     * Test related method
     * @param mixed $form
     * @param mixed $data
     *
     * @return FormEvent
     */
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

    /**
     * Test related method
     * @return Form
     */
    protected function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test related method
     * @param mixed $operation
     *
     * @return MassEditActionOperator
     */
    protected function getMassEditActionOperatorMock($operation)
    {
        $operator = $this
            ->getMockBuilder('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $operator->expects($this->any())
            ->method('getOperation')
            ->will($this->returnValue($operation));

        return $operator;
    }

    /**
     * Test related method
     * @param mixed $formType
     * @param array $formOptions
     *
     * @return MassEditAction
     */
    protected function getMassEditActionMock($formType, array $formOptions)
    {
        $operation = $this->getMock('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface');

        $operation->expects($this->any())
            ->method('getFormType')
            ->will($this->returnValue($formType));

        $operation->expects($this->any())
            ->method('getFormOptions')
            ->will($this->returnValue($formOptions));

        return $operation;
    }
}
