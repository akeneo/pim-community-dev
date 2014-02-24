<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Manager;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\AttributeType\TextType;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $repository    = $this->getEntityRepositoryMock();
        $objectManager = $this->getObjectManagerMock($repository);
        $factory       = $this->getAttributeTypeFactoryMock();

        $this->attributeManager = new AttributeManager(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
            'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue',
            'Pim\Bundle\CatalogBundle\Model\Product',
            $objectManager,
            $factory
        );
    }

    /**
     * Get a mock of ObjectManager
     * @param mixed $repository
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock($repository)
    {
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $manager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a mock of AttributeTypeFactory
     *
     * @return Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory
     */
    protected function getAttributeTypeFactoryMock()
    {
        $factory = $this
            ->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $guesser = new AttributeConstraintGuesser();
        $attributeType = new TextType('varchar', 'text', $guesser);
        $factory->expects($this->any())
            ->method('get')
            ->will($this->returnValue($attributeType));
        $factory->expects($this->any())
            ->method('getAttributeTypes')
            ->will($this->returnValue(array('mytype')));

        return $factory;
    }

    /**
     * Test related method
     */
    public function testCreateAttribute()
    {
        $attribute = $this->attributeManager->createAttribute();
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $attribute);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOption()
    {
        $option = $this->attributeManager->createAttributeOption();
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOption', $option);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOptionValue()
    {
        $value = $this->attributeManager->createAttributeOptionValue();
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue', $value);
    }

    /**
     * Test getAttributeTypes method
     */
    public function testGetAttributeTypes()
    {
        $types = $this->attributeManager->getAttributeTypes();
        $this->assertNotEmpty($types);
        foreach ($types as $type) {
            $this->assertNotEmpty($type);
        }
    }
}
