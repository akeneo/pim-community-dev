<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Entity;

use Pim\Bundle\TestBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeTest extends KernelAwareTest
{

    /**
     * Attribute name value
     * @staticvar string
     */
    protected static $attributeName = 'att-name';

    /**
     * Attribute description value
     * @var string
     */
    protected static $attributeDescription = 'att-description';

    /**
     * Attribute smart value
     * @var boolean
     */
    protected static $attributeSmart = true;

    /**
     * Test getter and setter for name attribute
     */
    public function testName()
    {
        // create product attribute entity
        $attribute = $this->getProductManager()->createFlexibleAttribute();

        // assert default value
        $this->assertNull($attribute->getName());

        // set a name value to product attribute
        $attribute->setName(self::$attributeName);
        $this->assertEquals(self::$attributeName, $attribute->getName());
    }

    /**
     * Test getter and setter for description attribute
     */
    public function testDescription()
    {
        // create product attribute entity
        $attribute = $this->getProductManager()->createFlexibleAttribute();

        // assert default value
        $this->assertNull($attribute->getDescription());

        // set a description value to product attribute
        $attribute->setDescription(self::$attributeDescription);
        $this->assertEquals(self::$attributeDescription, $attribute->getDescription());
    }

    /**
     * Test getter and setter for smart attribute
     */
    public function testSmart()
    {
        // create product attribute entity
        $attribute = $this->getProductManager()->createFlexibleAttribute();

        // assert default value
        $this->assertNull($attribute->getSmart());

        // set a smart value to product attribute
        $attribute->setSmart(self::$attributeSmart);
        $this->assertEquals(self::$attributeSmart, $attribute->getSmart());

    }

    /**
     * Get product manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim.flexible_product.product_manager');
    }
}