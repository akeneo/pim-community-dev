<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CalendarBundle\Form\Type\CalendarEventType;

class CalendarEventTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarEventType
     */
    protected $type;

    public function setUp()
    {
        $this->type = new CalendarEventType(array());
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with('title', 'textarea', array('required' => true))
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('start', 'oro_datetime', array('required' => true))
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('end', 'oro_datetime', array('required' => true))
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('allDay', 'checkbox', array('required' => false))
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('reminder', 'checkbox', array('required' => false))
            ->will($this->returnSelf());

        $this->type->buildForm($builder, array());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'data_class' => 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                    'intention'  => 'calendar_event',
                )
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_calendar_event', $this->type->getName());
    }
}
