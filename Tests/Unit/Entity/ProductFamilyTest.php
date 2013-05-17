<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFamilyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $productFamily = new ProductFamily();
        $this->assertEntity($productFamily);
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $productFamily = new ProductFamily();
        $this->assertEmpty($productFamily->getId());
    }

    /**
     * Test getter/setter for name property
     */
    public function testGetSetName()
    {
        $productFamily = new ProductFamily();
        $this->assertEmpty($productFamily->getCode());

        // Change value and assert new
        $newName = 'test-name';
        $productFamily->setCode($newName);
        $this->assertEquals($newName, $productFamily->getCode());
    }

    /**
     * Test getter/setter for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        $productFamily = new ProductFamily();

        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $productFamily->addAttribute($newAttribute);
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $productFamily->getAttributes()->first()
        );

        $productFamily->removeAttribute($newAttribute);
        $this->assertNotInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $productFamily->getAttributes()->first()
        );
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $productFamily = new ProductFamily();
        $string = 'test-string';
        $productFamily->setCode($string);
        $this->assertEquals($string, $productFamily->__toString());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\ProductFamily $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $entity);
    }
}
