<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Oro\Bundle\CalendarBundle\Form\Type\CalendarEventApiType;

class CalendarEventApiTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $meta = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $calendar = new Calendar();
        ReflectionUtil::setId($calendar, 1);

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with('OroCalendarBundle:Calendar')
            ->will($this->returnValue($em));
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with('OroCalendarBundle:Calendar')
            ->will($this->returnValue($meta));
        $em->expects($this->any())
            ->method('getRepository')
            ->with('OroCalendarBundle:Calendar')
            ->will($this->returnValue($repo));
        $meta->expects($this->any())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue('id'));
        $repo->expects($this->any())
            ->method('find')
            ->with($calendar->getId())
            ->will($this->returnValue($calendar));

        $childType = new EntityIdentifierType($registry);

        return array(
            new PreloadedExtension(
                array(
                    $childType->getName() => $childType,
                ),
                array()
            )
        );
    }

    public function testSubmitValidData()
    {
        $formData = array(
            'calendar' => 1,
            'title'    => 'testTitle',
            'start'    => '2013-10-05T13:00:00Z',
            'end'      => '2013-10-05T13:30:00+00:00',
            'allDay'   => true,
            'reminder' => true,
        );

        $type = new CalendarEventApiType(array());
        $form = $this->factory->create($type);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var CalendarEvent $result */
        $result = $form->getData();
        $this->assertInstanceOf('Oro\Bundle\CalendarBundle\Entity\CalendarEvent', $result);
        $calendar = new Calendar();
        ReflectionUtil::setId($calendar, 1);
        $this->assertEquals($calendar, $result->getCalendar());
        $this->assertEquals('testTitle', $result->getTitle());
        $this->assertDateTimeEquals(new \DateTime('2013-10-05T13:00:00Z'), $result->getStart());
        $this->assertDateTimeEquals(new \DateTime('2013-10-05T13:30:00Z'), $result->getEnd());
        $this->assertTrue($result->getAllDay());
        $this->assertTrue($result->getReminder());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'data_class'      => 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                    'intention'       => 'calendar_event',
                    'csrf_protection' => false,
                )
            );

        $type = new CalendarEventApiType(array());
        $type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $type = new CalendarEventApiType(array());
        $this->assertEquals('oro_calendar_event_api', $type->getName());
    }
}
