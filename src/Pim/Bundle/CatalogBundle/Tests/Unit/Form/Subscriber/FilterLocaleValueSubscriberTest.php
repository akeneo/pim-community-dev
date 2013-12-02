<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Form\Subscriber\FilterLocaleValueSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleValueSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_LOCALE    = 'fr_FR';
    const COMPARISON_LOCALE = 'fr_BE';
    const OTHER_LOCALE      = 'en_US';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new FilterLocaleValueSubscriber(self::CURRENT_LOCALE, self::COMPARISON_LOCALE);
    }

    /**
     * Test related method
     */
    public function testInstandOfEventSubscriberInterface()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->target);
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array('form.pre_set_data' => 'preSetData'), $this->target->getSubscribedEvents());
    }

    /**
     * Test related method
     */
    public function testPreSetDataWithNullData()
    {
        $form  = $this->getFormMock();
        $event = $this->getEventMock(null, $form);

        $form->expects($this->never())
            ->method('remove');

        $this->target->preSetData($event);
    }

    /**
     * Test related method
     */
    public function testPreSetData()
    {
        $data = array(
            'name_current'               => $this->getProductValueMock($this->getProductAttributeMock(), self::CURRENT_LOCALE),
            'name_other'                 => $this->getProductValueMock($this->getProductAttributeMock(), self::OTHER_LOCALE),
            'not_translatable_attribute' => $this->getProductValueMock($this->getProductAttributeMock(false), null),
        );

        $form  = $this->getFormMock();
        $event = $this->getEventMock($data, $form);

        $form->expects($this->exactly(1))
            ->method('remove')
            ->with('name_other');

        $this->target->preSetData($event);
    }

    public function testSetComparisonAttributesDisabled()
    {
        $data = array(
            'name_current'    => $this->getProductValueMock($this->getProductAttributeMock(), self::CURRENT_LOCALE),
            'name_comparison' => $this->getProductValueMock($this->getProductAttributeMock(), self::COMPARISON_LOCALE),
        );

        $form  = $this->getFormMock();
        $event = $this->getEventMock($data, $form);

        $form->expects($this->exactly(1))
            ->method('add')
            ->with('name_comparison', 'pim_product_value', array(
                'disabled'     => true,
                'block_config' => array(
                    'mode' => 'comparison'
                )
            ));

        $this->target->preSetData($event);
    }

    /**
     * @param mixed $data
     * @param mixed $form
     *
     * @return \Symfony\Component\Form\FormEvent
     */
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

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param mixed $attribute
     * @param mixed $locale
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValue
     */
    private function getProductValueMock($attribute, $locale)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        return $value;
    }

    /**
     * @param boolean $translatable
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    private function getProductAttributeMock($translatable = true)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }
}
