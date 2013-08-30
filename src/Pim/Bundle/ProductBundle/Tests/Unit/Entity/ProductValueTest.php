<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
            'Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue',
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

        $target->setEntity($product);
        $target->setAttribute($attribute);

        $this->assertTrue($target->isRemovable());
    }

    private function getTargetedClass()
    {
        return new ProductValue();
    }

    private function getFamilyMock()
    {
        return $this
            ->getMock('Pim\Bundle\ProductBundle\Entity\Family');
    }

    private function getProductMock()
    {
        return $this
            ->getMock('Pim\Bundle\ProductBundle\Entity\Product');
    }

    private function getProductAttributeMock()
    {
        return $this
            ->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');
    }

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
