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
     * @var Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected $localeManager;

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
        $localeManager = $this->getLocaleManagerMock();
        $factory       = $this->getAttributeTypeFactoryMock();

        $this->attributeManager = new AttributeManager(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
            'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue',
            'Pim\Bundle\CatalogBundle\Model\Product',
            $objectManager,
            $localeManager,
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
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $manager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale');
        $locale->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(('en_US')));
        $manager->expects($this->any())
            ->method('getLocaleByCode')
            ->will($this->returnValue($locale));
        $manager->expects($this->any())
            ->method('getActiveLocales')
            ->will($this->returnValue([$locale]));

        return $manager;
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
            ->will($this->returnValue(['mytype']));

        return $factory;
    }

    /**
     * Test createAttributeFromFormData method
     */
    public function testCreateAttributeFromFormData()
    {
        $data = ['attributeType' => 'pim_catalog_metric'];
        $attribute = $this->attributeManager->createAttributeFromFormData($data);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $attribute);

        $attribute = $this->attributeManager->createAttribute('pim_catalog_price_collection');
        $newAttribute = $this->attributeManager->createAttributeFromFormData($attribute);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Attribute', $newAttribute);
        $this->assertEquals($attribute, $newAttribute);

        $attribute = 'ImageType';
        $newAttribute = $this->attributeManager->createAttributeFromFormData($attribute);
        $this->assertNull($newAttribute);
    }

    /**
     * Test prepareFormData method
     */
    public function testPrepareFormData()
    {
        $data = ['attributeType' => 'pim_catalog_multiselect'];
        $data = $this->attributeManager->prepareFormData($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('options', $data);
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
