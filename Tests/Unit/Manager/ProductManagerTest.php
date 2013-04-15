<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Manager;

use Pim\Bundle\ProductBundle\EventListener\AddMissingTranslatableAttributeLocaleValueSubscriber;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Manager\ProductManager;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */ function it_should_do_nothing_if_attribute_of_entity_to_save_is_not_translatable()
    {
        $target  = $this->getTargetedClass();
        $value   = $this->getValueMock(false);
        $product = $this->getProductMock(array($value));

        $product->expects($this->never())
                ->method('addValue');

        $value->expects($this->never())
              ->method('getLocale');

        $target->save($product);
    }

    /** @test */ function it_should_add_missing_locale_values_for_translatable_attributes_before_inserting_a_product()
    {
        $target   = $this->getTargetedClass();
        $product  = $this->getProductMock(array(
            $this->getValueMock(true),
            $this->getValueMock(false),
        ));

        $product->expects($this->once())
                ->method('addValue');

        $target->save($product);
    }

    private function getTargetedClass()
    {
        return new ProductManager('Product', array('entities_config' => array('Product' => null)), $this->getObjectManagerMock(), $this->getEventDispatcherInterfaceMock());
    }

    private function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    public function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    private function getValueMock($translatable = false)
    {
        $methods = array('getAttribute');
        if ($translatable) {
            $methods[] = 'getLocale';
        }

        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue', $methods);

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($this->getAttributeMock($translatable)));

        if ($translatable) {
            $value->expects($this->any())
                  ->method('getLocale')
                  ->will($this->returnValue('fr'));
        }

        return $value;
    }

    private function getAttributeMock($translatable = false)
    {
        $attribute = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Attribute', array('getTranslatable'));

        $attribute->expects($this->any())
                  ->method('getTranslatable')
                  ->will($this->returnValue($translatable));

        return $attribute;
    }

    private function getProductMock(array $values)
    {
        $product = $this->getMock('Pim\Bundle\ProductBundle\Entity\Product', array('getValues', 'getLanguages', 'addValue'));

        $product->expects($this->any())
                ->method('getValues')
                ->will($this->returnValue(new ArrayCollection($values)));

        $product->expects($this->any())
                ->method('getLanguages')
                ->will($this->returnValue(new ArrayCollection(array(
                    $this->getLanguageMock('fr'),
                    $this->getLanguageMock('en'),
                ))));

        return $product;
    }

    private function getLanguageMock($code)
    {
        $language = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductLanguage',  array('getCode'));

        $language->expects($this->any())
                 ->method('getCode')
                 ->will($this->returnValue($code));

        return $language;
    }
}
