<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    protected $attributeOption;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->attributeOption = new AttributeOption();
    }

    /**
     * Test is/set default property
     */
    public function testIsSetDefault()
    {
        $this->assertFalse($this->attributeOption->isDefault());

        $expectedIsDefault = true;
        $this->assertEntity($this->attributeOption->setDefault($expectedIsDefault));
        $this->assertTrue($this->attributeOption->isDefault());

        $expectedIsDefault = false;
        $this->assertEntity($this->attributeOption->setDefault($expectedIsDefault));
        $this->assertFalse($this->attributeOption->isDefault());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\CatalogBundle\Entity\AttributeOption $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOption', $entity);
    }
}
