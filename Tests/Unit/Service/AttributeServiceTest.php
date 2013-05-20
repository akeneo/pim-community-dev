<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Service;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Service\AttributeService;
use Symfony\Component\Validator\GlobalExecutionContext;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeServiceTest extends WebTestCase
{
    /**
     * @var ExecutionContext
     */
    protected $executionContext;

    /**
     * @var AttributeService
     */
    protected $service;

    /**
     * @var array Attributes config
     */
    protected $config;

    /**
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var Pim\Bundle\ConfigBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->executionContext = $this->initExecutionContext();

        static::$kernel = static::createKernel(array('environment' => 'dev'));
        static::$kernel->boot();

        $this->config = static::$kernel->getContainer()->getParameter('pim_product.attributes_config');
        $this->manager = static::$kernel->getContainer()->get('product_manager');
        $this->localeManager = static::$kernel->getContainer()->get('pim_config.manager.locale');
        $this->factory = static::$kernel->getContainer()
            ->get('oro_flexibleentity.attributetype.factory');

        $this->service = new AttributeService($this->config, $this->manager, $this->localeManager, $this->factory);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
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
     * Test attribute configuration file
     */
    public function testAttributeConfig()
    {
        $this->assertArrayHasKey('attributes_config', $this->config);
        foreach ($this->config['attributes_config'] as $type => $options) {
            $this->assertInternalType('string', $type);

            $this->assertArrayHasKey('name', $options);
            $this->assertArrayHasKey('properties', $options);
            $this->assertArrayHasKey('parameters', $options);

            $this->assertInternalType('string', $options['name']);
            $this->assertInternalType('array', $options['properties']);
            $this->assertInternalType('array', $options['parameters']);
        }
    }

   /**
     * Test createAttributeFromFormData method
     */
    public function testCreateAttributeFromFormData()
    {
        $data = array('attributeType' => 'oro_flexibleentity_metric');
        $attribute = $this->service->createAttributeFromFormData($data);
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductAttribute', $attribute);

        $attribute = $this->createProductAttribute('pim_product_price_collection');
        $newAttribute = $this->service->createAttributeFromFormData($attribute);
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductAttribute', $newAttribute);
        $this->assertEquals($attribute, $newAttribute);

        $attribute = 'ImageType';
        $newAttribute = $this->service->createAttributeFromFormData($attribute);
        $this->assertNull($newAttribute);
    }

   /**
     * Test prepareFormData method
     */
    public function testPrepareFormData()
    {
        $data = array('attributeType' => 'pim_product_multiselect');
        $data = $this->service->prepareFormData($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('options', $data);
    }

    /**
     * Test getPropertyFields method
     */
    public function testGetPropertyFields()
    {
        $attributeTypes = array_keys($this->config['attributes_config']);

        foreach ($attributeTypes as $type) {
            $attribute = $this->createProductAttribute($type);
            $fields = $this->service->getPropertyFields($attribute);

            $this->assertNotEmpty($fields);
            foreach ($fields as $field) {
                $this->assertArrayHasKey('name', $field);
            }
        }

        // Test custom cases = with attribute type missing and with DateType
        $attribute = $this->createProductAttribute();
        $fields = $this->service->getPropertyFields($attribute);
        $this->assertEmpty($fields);

        $attribute = $this->createProductAttribute('oro_flexibleentity_date');
        $attribute->setDateType('date');
        $fields = $this->service->getPropertyFields($attribute);
        $this->assertNotEmpty($fields);

        $attribute = $this->createProductAttribute('oro_flexibleentity_date');
        $attribute->setDateType('time');
        $fields = $this->service->getPropertyFields($attribute);
        $this->assertNotEmpty($fields);
    }

    /**
     * Test getParameterFields method
     */
    public function testGetParameterFields()
    {
        $attributeTypes = array_keys($this->config['attributes_config']);

        $numOfParams = 0;
        foreach ($this->config['attributes_config'] as $item) {
            $i = count($item['parameters']);
            $numOfParams = $i > $numOfParams ? $i : $numOfParams;
        }

        foreach ($attributeTypes as $type) {
            $attribute = $this->createProductAttribute($type);
            $fields = $this->service->getParameterFields($attribute);

            $this->assertNotEmpty($fields);
            $this->assertCount($numOfParams, $fields);
            foreach ($fields as $field) {
                $this->assertArrayHasKey('name', $field);
            }
        }

        // Test case with attribute type missing
        $attribute = $this->createProductAttribute();
        $fields = $this->service->getParameterFields($attribute);
        $this->assertNotEmpty($fields);
        $this->assertCount($numOfParams, $fields);
        foreach ($fields as $field) {
            $this->assertArrayHasKey('options', $field);
            $this->assertTrue($field['options']['disabled']);
            $this->assertTrue($field['options']['read_only']);
        }
    }

    /**
     * Test getAttributeTypes method
     */
    public function testGetAttributeTypes()
    {
        $types = $this->service->getAttributeTypes();
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
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute($type = null)
    {
        return $this->manager->createAttribute($type);
    }
}
