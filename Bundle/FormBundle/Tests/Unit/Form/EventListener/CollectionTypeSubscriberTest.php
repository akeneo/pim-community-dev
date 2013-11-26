<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\FormBundle\Form\EventListener\CollectionTypeSubscriber;

class CollectionTypeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionTypeSubscriber
     */
    protected $subscriber;

    /**
     * SetUp test environment
     */
    public function setUp()
    {
        $this->subscriber = new CollectionTypeSubscriber();
    }

    public function testGetSubscribedEvents()
    {
        $result = $this->subscriber->getSubscribedEvents();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey(FormEvents::POST_SUBMIT, $result);
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $result);
    }

    public function testPostSubmit()
    {
        $itemEmpty = $this->getMock('Oro\Bundle\FormBundle\Entity\EmptyItem');
        $itemEmpty->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(true));
        $itemNotEmpty = $this->getMock('Oro\Bundle\FormBundle\Entity\EmptyItem');
        $itemNotEmpty->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(false));
        $itemNotEmptyType = $this->getMock('SomeClass');
        $itemNotEmptyType->expects($this->never())->method($this->anything());

        $data = new ArrayCollection(array($itemEmpty, $itemNotEmpty, $itemNotEmptyType));
        $this->subscriber->postSubmit($this->createEvent($data));

        $this->assertEquals(
            array(
                1 => $itemNotEmpty,
                2 => $itemNotEmptyType
            ),
            $data->toArray()
        );
    }

    public function testPostSubmitNotCollectionData()
    {
        $data = $this->getMock('SomeClass');
        $data->expects($this->never())->method($this->anything());

        $this->subscriber->postSubmit($this->createEvent($data));
    }

    /**
     * @dataProvider preSubmitNoDataDataProvider
     * @param array|null $data
     */
    public function testPreSubmitNoData($data)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->never())
            ->method('setData');

        $this->subscriber->preSubmit($event);
    }

    /**
     * @return array
     */
    public function preSubmitNoDataDataProvider()
    {
        return array(
            array(
                null, array()
            ),
            array(
                array(), array()
            )
        );
    }

    /**
     * @dataProvider preSubmitDataProvider
     *
     * @param array $data
     * @param array $expected
     * @param bool $checkIsNew
     * @param mixed $parentDataId
     */
    public function testPreSubmit(array $data, array $expected, $checkIsNew = false, $parentDataId = null)
    {
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        if ($checkIsNew) {
            $parentForm = $this->getMock('Symfony\Component\Form\Test\FormInterface');
            $parentFormData = $this->getMock('SomeClass', array('getId'));

            $form->expects($this->once())->method('getParent')
                ->will($this->returnValue($parentForm));

            $parentForm->expects($this->once())->method('getData')
                ->will($this->returnValue($parentFormData));

            $parentFormData->expects($this->once())->method('getId')
                ->will($this->returnValue($parentDataId));
        } else {
            $form->expects($this->never())->method($this->anything());
        }

        $event = $this->createEvent($data, $form);
        $this->subscriber->preSubmit($event);
        $this->assertEquals($expected, $event->getData());
    }

    public function preSubmitDataProvider()
    {
        return array(
            'set_primary_for_new_data' => array(
                'data' => array(array('k' => 'v')),
                'expected' => array(array('k' => 'v', 'primary' => true)),
                'check_is_new' => true,
                'parent_data_id' => null
            ),
            'set_primary_for_one_item' => array(
                'data' => array(array('k' => 'v')),
                'expected' => array(array('k' => 'v', 'primary' => true)),
                'check_is_new' => true,
                'parent_data_id' => 1
            ),
            'not_set_primary_for_not_new_data' => array(
                'data' => array(array('k' => 'v'), array('k2' => 'v2')),
                'expected' => array(array('k' => 'v'), array('k2' => 'v2')),
                'check_is_new' => true,
                'parent_data_id' => 1
            ),
            'primary_is_already_set' => array(
                'data' => array(array('primary' => true), array(array('k' => 'v'))),
                'expected' => array(array('primary' => true), array(array('k' => 'v')))
            ),
            'skip_empty_data_array' => array(
                'data' => array(array(array()), array(), array('k' => 'v', 'primary' => true), array()),
                'expected' => array('2' => array('k' => 'v', 'primary' => true))
            )
        );
    }

    /**
     * @dataProvider preSubmitNoResetDataProvider
     * @param array $data
     */
    public function testPreSubmitNoReset($data)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->never())
            ->method('setData');
        $this->subscriber->preSubmit($event);
    }

    /**
     * @return array
     */
    public function preSubmitNoResetDataProvider()
    {
        return array(
            array(array()),
            array('foo')
        );
    }

    /**
     * @param mixed $data
     * @param FormInterface|null $form
     * @return FormEvent
     */
    protected function createEvent($data, FormInterface $form = null)
    {
        $form = $form ? $form : $this->getMock('Symfony\Component\Form\Test\FormInterface');
        return new FormEvent($form, $data);
    }
}
