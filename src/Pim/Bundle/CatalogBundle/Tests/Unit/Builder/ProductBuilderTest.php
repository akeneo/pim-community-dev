<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Builder;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test relatede method
     */
    public function testAddMissingProductValues()
    {
        $builder = $this->getProductBuilder();
        $product = new Product();
        $this->assertEquals(count($product->getValues()), 0);

        $family = new Family();
        $attribute = new ProductAttribute();
        $attribute->setCode('myatt');
        $family->addAttribute($attribute);
        $product->setFamily($family);

        $builder->addMissingProductValues($product);
        $this->assertEquals(count($product->getValues()), 1);

        $attributeTwo = new ProductAttribute();
        $attributeTwo->setCode('two');
        $family->addAttribute($attributeTwo);

        $builder->addMissingProductValues($product);
        $this->assertEquals(count($product->getValues()), 2);
    }

    /**
     * @return ProductBuilder
     */
    protected function getProductBuilder()
    {
        $productClass = 'Pim\Bundle\CatalogBundle\Entity\Product';

        return new ProductBuilder($productClass, $this->getObjectManagerMock(), $this->getCurrencyManagerMock());
    }

    /**
     * @param array $activeCodes
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\CurrencyManager
     */
    protected function getCurrencyManagerMock(array $activeCodes = array())
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue($activeCodes));

        return $manager;
    }

    /**
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $manager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->getClassMetadataMock()));

        return $manager;
    }

    /**
     * Get a mock of ClassMetadata
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function getClassMetadataMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getAssociationMappings')
            ->will(
                $this->returnValue(
                    array('values' => array('targetEntity' => 'Pim\Bundle\CatalogBundle\Entity\ProductValue'))
                )
            );

        return $mock;
    }
}
