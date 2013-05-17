<?php

namespace Pim\Bundle\TranslationBundle\Form\Subscriber;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Forms;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddTranslatableFieldSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subscriber events
     */
    public function testGetSubscriberEvents()
    {
        $target = $this->getTargetedClass();
        $events = $target->getSubscribedEvents();

        $this->assertTrue(array_key_exists('form.pre_set_data', $events), 'preSetData');
        $this->assertTrue(array_key_exists('form.post_bind', $events), 'postBind');
        $this->assertTrue(array_key_exists('form.bind', $events), 'bind');
    }

    /**
     * @test
     */
    public function itsPreSetDataShouldDoNothingIfDataIsNull()
    {
        $target = $this->getTargetedClass();
        $form   = $this->getFormMock();
        $event  = $this->getEventMock(null, $form);

        $form->expects($this->never())
             ->method('getParent');

        $target->preSetData($event);
    }

    protected function getTargetedClass()
    {
        return new AddTranslatableFieldSubscriber(
            $this->getFormFactoryMock(), $this->getValidatorMock(), array()
        );
    }

    protected function getFormFactoryMock()
    {
        return $this->getMock('Symfony\Component\Form\FormFactoryInterface');
    }

    protected function getValidatorMock()
    {
        return $this->getMock('Symfony\Component\Validator\ValidatorInterface');
    }

    protected function getEventMock($data, $form)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\Event\DataEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getData', 'getForm'))
            ->getMock()
        ;

        $event->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($data));

        $event->expects($this->any())
              ->method('getForm')
              ->will($this->returnValue($form));

        return $event;
    }

    protected function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function getDataMock($namespace = 'Pim\Bundle\TranslationBundle\Entity\AbstractTranslatableEntity')
    {
        return $this
            ->getMock($namespace)
        ;
    }
}
