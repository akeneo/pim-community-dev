<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Factory;

use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factory = new AttributeRequirementFactory();
    }

    /**
     * Test related method
     */
    public function testCreateAttributeRequirement()
    {
        $productAttribute = $this->getProductAttributeMock();
        $channel          = $this->getChannelMock();
        $requirement      = $this->factory->createAttributeRequirement($productAttribute, $channel, true);

        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement', $requirement);
        $this->assertEquals($productAttribute, $requirement->getAttribute());
        $this->assertEquals($channel, $requirement->getChannel());
        $this->assertTrue($requirement->isRequired());
    }

    /**
     * Test related method
     */
    public function testCreateUnrequiredAttributeRequirement()
    {
        $productAttribute = $this->getProductAttributeMock();
        $channel          = $this->getChannelMock();
        $requirement      = $this->factory->createAttributeRequirement($productAttribute, $channel, false);

        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement', $requirement);
        $this->assertEquals($productAttribute, $requirement->getAttribute());
        $this->assertEquals($channel, $requirement->getChannel());
        $this->assertFalse($requirement->isRequired());
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function getProductAttributeMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');
    }
}
