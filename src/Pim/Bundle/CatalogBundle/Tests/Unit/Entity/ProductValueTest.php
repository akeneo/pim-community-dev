<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\ProductValue;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldBeAnEntityFlexibleValue()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue',
            $this->getTargetedClass()
        );
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfValueHasNoProduct()
    {
        $target = $this->getTargetedClass();

        $this->assertTrue($target->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldNotBeRemovableIfTheAttributeBelongsToTheFamily()
    {
        $family    = $this->getFamilyMock();
        $attribute = $this->getProductAttributeMock();
        $product   = $this->getProductMock();
        $target    = $this->getTargetedClass();

        $product->expects($this->any())
                ->method('getFamily')
                ->will($this->returnValue($family));

        $family->expects($this->any())
               ->method('getAttributes')
               ->will($this->returnValue($this->getArrayCollectionMock($attribute)));

        $product->expects($this->any())
                ->method('isAttributeRemovable')
                ->with($attribute)
                ->will($this->returnValue(!$family->getAttributes()->contains($attribute)));

        $target->setEntity($product);
        $target->setAttribute($attribute);

        $this->assertFalse($target->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfTheAttributeDoesNotBelongToTheFamily()
    {
        $family    = $this->getFamilyMock();
        $attribute = $this->getProductAttributeMock();
        $product   = $this->getProductMock();
        $target    = $this->getTargetedClass();

        $product->expects($this->any())
                ->method('getFamily')
                ->will($this->returnValue($family));

        $family->expects($this->any())
               ->method('getAttributes')
               ->will($this->returnValue($this->getArrayCollectionMock($attribute, false)));

        $product->expects($this->any())
                ->method('isAttributeRemovable')
                ->with($attribute)
                ->will($this->returnValue(!$family->getAttributes()->contains($attribute)));

        $target->setEntity($product);
        $target->setAttribute($attribute);

        $this->assertTrue($target->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfProductHasNoFamily()
    {
        $attribute = $this->getProductAttributeMock();
        $product   = $this->getProductMock();
        $target    = $this->getTargetedClass();

        $product->expects($this->any())
                ->method('getFamily')
                ->will($this->returnValue(null));

        $product->expects($this->any())
                ->method('isAttributeRemovable')
                ->with($attribute)
                ->will($this->returnValue($product->getFamily() === null));

        $target->setEntity($product);
        $target->setAttribute($attribute);

        $this->assertTrue($target->isRemovable());
    }

    /**
     * @return ProductValue
     */
    private function getTargetedClass()
    {
        return new ProductValue();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    private function getFamilyMock()
    {
        return $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Family');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    private function getProductMock()
    {
        return $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    private function getProductAttributeMock()
    {
        return $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
    }

    /**
     * @param mixed   $element
     * @param boolean $contains
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    private function getArrayCollectionMock($element, $contains = true)
    {
        $coll = $this->getMock('Doctrine\Common\Collections\ArrayCollection', array('contains'));
        $coll->expects($this->any())
             ->method('contains')
             ->with($this->equalTo($element))
             ->will($this->returnValue($contains));

        return $coll;
    }
}
