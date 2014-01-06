<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\ProductValue;
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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->value = new ProductValue();
    }

    /**
     * @test
     */
    public function itShouldBeAnEntityFlexibleValue()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue',
            $this->value
        );
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfValueHasNoProduct()
    {
        $this->assertTrue($this->value->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldNotBeRemovableIfTheAttributeBelongsToTheFamily()
    {
        $family    = $this->getFamilyMock();
        $attribute = $this->getAttributeMock();
        $product   = $this->getProductMock();

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

        $this->value->setEntity($product);
        $this->value->setAttribute($attribute);

        $this->assertFalse($this->value->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfTheAttributeDoesNotBelongToTheFamily()
    {
        $family    = $this->getFamilyMock();
        $attribute = $this->getAttributeMock();
        $product   = $this->getProductMock();

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

        $this->value->setEntity($product);
        $this->value->setAttribute($attribute);

        $this->assertTrue($this->value->isRemovable());
    }

    /**
     * @test
     */
    public function itShouldBeRemovableIfProductHasNoFamily()
    {
        $attribute = $this->getAttributeMock();
        $product   = $this->getProductMock();

        $product->expects($this->any())
                ->method('getFamily')
                ->will($this->returnValue(null));

        $product->expects($this->any())
                ->method('isAttributeRemovable')
                ->with($attribute)
                ->will($this->returnValue($product->getFamily() === null));

        $this->value->setEntity($product);
        $this->value->setAttribute($attribute);

        $this->assertTrue($this->value->isRemovable());
    }

    /**
     * Test related method
     */
    public function testGetSetMedia()
    {
        $media = $this->getMediaMock();
        $media->expects($this->once())
            ->method('setValue')
            ->with($this->value);

        $this->value->setMedia($media);
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
            ->getMock('Pim\Bundle\CatalogBundle\Model\Product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Attribute
     */
    private function getAttributeMock()
    {
        return $this
            ->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');
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

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMediaMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');
    }
}
