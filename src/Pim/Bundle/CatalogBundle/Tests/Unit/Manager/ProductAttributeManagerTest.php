<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Manager;

use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

use Pim\Bundle\CatalogBundle\Manager\ProductAttributeManager;
use Symfony\Component\Validator\GlobalExecutionContext;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeManagerTest extends WebTestCase
{
    /**
     * @var ExecutionContext
     */
    protected $executionContext;

    /**
     * @var ProductAttributeManager
     */
    protected $attributeManager;

    /**
     * @var array Attributes config
     */
    protected $config;

    /**
     * @var ProductManager
     */
    protected $productManager;

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
        $this->markTestSkipped('Due to Symfony 2.3 Upgrade, GlobalExecutionContext issue');
        parent::setUp();

        $this->executionContext = $this->initExecutionContext();

        static::$kernel = static::createKernel(array('environment' => 'dev'));
        static::$kernel->boot();

        $this->productManager = static::$kernel->getContainer()->get('pim_catalog.manager.product');
        $this->localeManager = static::$kernel->getContainer()->get('pim_catalog.manager.locale');
        $this->factory = static::$kernel->getContainer()
            ->get('pim_flexibleentity.attributetype.factory');

        $this->attributeManager = new ProductAttributeManager($this->productManager, $this->localeManager, $this->factory);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->executionContext = null;

        parent::tearDown();
    }

    /**
     * Initialize execution context for validator with mock objects
     *
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function initExecutionContext()
    {
        $graphWalker = $this->getMock('Symfony\Component\Validator\GraphWalker', array(), array(), '', false);
        $metadataFactory = $this->getMock('Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface');
        $globalContext = new GlobalExecutionContext('Root', $graphWalker, $metadataFactory);

        return new ExecutionContext($globalContext, 'currentValue', 'foo.bar', 'Group', 'ClassName', 'propertyName');
    }

    /**
     * Test createAttributeFromFormData method
     */
    public function testCreateAttributeFromFormData()
    {
        $data = array('attributeType' => 'pim_catalog_metric');
        $attribute = $this->attributeManager->createAttributeFromFormData($data);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\ProductAttribute', $attribute);

        $attribute = $this->createProductAttribute('pim_catalog_price_collection');
        $newAttribute = $this->attributeManager->createAttributeFromFormData($attribute);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\ProductAttribute', $newAttribute);
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
        $data = array('attributeType' => 'pim_catalog_multiselect');
        $data = $this->attributeManager->prepareFormData($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('options', $data);
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

    /**
     * Create a product attribute entity
     *
     * @param AttributeType|null $type Product attribute type
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute($type = null)
    {
        return $this->attributeManager->createAttribute($type);
    }
}
