<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\FilterLocaleValueSubscriber;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleValueSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_LOCALE = 'fr_FR';
    const OTHER_LOCALE   = 'en_US';

    public function setUp()
    {
        $this->target = new FilterLocaleValueSubscriber(self::CURRENT_LOCALE);
    }

    public function testInstandOfEventSubscriberInterface()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->target);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array('form.pre_set_data' => 'preSetData'), $this->target->getSubscribedEvents());
    }

    public function testPreSetDataWithNullData()
    {
        $form  = $this->getFormMock();
        $event = $this->getEventMock(null, $form);

        $form->expects($this->never())
            ->method('remove');

        $this->target->preSetData($event);
    }

    public function testPreSetData()
    {
        $data = array(
            'name_current' => $this->getProductValueMock($this->getProductAttributeMock(), self::CURRENT_LOCALE),
            'name_other' => $this->getProductValueMock($this->getProductAttributeMock(), self::OTHER_LOCALE),
            'not_translatable_attribute' => $this->getProductValueMock($this->getProductAttributeMock(false), null),
        );

        $form  = $this->getFormMock();
        $event = $this->getEventMock($data, $form);

        $form->expects($this->exactly(1))
            ->method('remove')
            ->with('name_other');

        $this->target->preSetData($event);
    }

    private function getEventMock($data, $form)
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        return $event;
    }

    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getProductValueMock($attribute, $locale)
    {
        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        return $value;
    }

    private function getProductAttributeMock($translatable = true)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }
}
