<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Factory;

use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productManager = $this->getProductManagerMock();
        $this->channelManager = $this->getChannelManagerMock();
        $this->attributeRequirementFactory = $this->getAttributeRequirementFactoryMock();
        $this->factory = new FamilyFactory(
            $this->productManager,
            $this->channelManager,
            $this->attributeRequirementFactory
        );
    }

    /**
     * Test related method
     */
    public function testCreateFamily()
    {
        $identifier = $this->getProductAttributeMock('sku');
        $this->productManager
            ->expects($this->any())
            ->method('getIdentifierAttribute')
            ->will($this->returnValue($identifier));

        $channel1 = $this->getChannelMock('channel1');
        $channel2 = $this->getChannelMock('channel2');
        $this->channelManager
            ->expects($this->any())
            ->method('getChannels')
            ->will($this->returnValue(array($channel1, $channel2)));

        $requirement1 = $this->getAttributeRequirementMock($identifier, $channel1);
        $requirement2 = $this->getAttributeRequirementMock($identifier, $channel2);
        $this->attributeRequirementFactory
            ->expects($this->any())
            ->method('createAttributeRequirement')
            ->will(
                $this->returnValueMap(
                    array(
                        array($identifier, $channel1, true, $requirement1),
                        array($identifier, $channel2, true, $requirement2),
                    )
                )
            );

        $family = $this->factory->createFamily();

        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family', $family);
        $this->assertCount(1, $family->getAttributes());
        $this->assertSame($identifier, $family->getAttributes()->first());

        $this->assertCount(2, $family->getAttributeRequirements());
        $this->assertEquals(
            array(
                'sku_channel1' => $requirement1,
                'sku_channel2' => $requirement2,
            ),
            $family->getAttributeRequirements()
        );
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ChannelManager
     */
    protected function getChannelManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory
     */
    protected function getAttributeRequirementFactoryMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory');
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function getProductAttributeMock($code)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $attribute;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChannelMock($code)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $channel;
    }

    /**
     * @param ProductAttribute $attribute
     * @param Channel          $channel
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeRequirement
     */
    protected function getAttributeRequirementMock($attribute, $channel)
    {
        $requirement = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement');

        $requirement->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $requirement->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        return $requirement;
    }
}
