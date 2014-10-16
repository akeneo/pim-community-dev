<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

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
     * Test __toString method
     */
    public function testToString()
    {
        $code = 'test_code';
        $this->attributeOption->setCode($code);
        $this->assertSame('['.$code.']', $this->attributeOption->__toString());

        $newValue = 'test_value';
        $optionValue = new AttributeOptionValue();
        $optionValue->setValue($newValue);
        $this->attributeOption->addOptionValue($optionValue);

        $this->assertSame($newValue, $this->attributeOption->__toString());
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
