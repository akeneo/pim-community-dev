<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Service;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Service\AttributeService;

use Pim\Bundle\ProductBundle\Model\AttributeType\OptionSimpleSelectType;
use Pim\Bundle\ProductBundle\Model\AttributeType\OptionMultiSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\DateType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\FileType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\ImageType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\IntegerType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MetricType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\NumberType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;

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

        $this->service = new AttributeService($this->config, $this->manager, $this->localeManager);
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
        $data = array('attributeType' => AbstractAttributeType::TYPE_METRIC_CLASS);
        $attribute = $this->service->createAttributeFromFormData($data);
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductAttribute', $attribute);

        $attribute = $this->createProductAttribute(new MoneyType());
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
        $data = array('attributeType' => AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS);
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
        $pimPath = 'Pim\Bundle\ProductBundle\Model\AttributeType\\';
        $oroPath = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\\';

        foreach ($attributeTypes as $type) {
            $type .= 'Type';
            $type = class_exists($pimPath . $type) ? $pimPath . $type : $oroPath . $type;

            $attribute = $this->createProductAttribute(new $type());
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

        $attribute = $this->createProductAttribute(new DateType());
        $attribute->setDateType('date');
        $fields = $this->service->getPropertyFields($attribute);
        $this->assertNotEmpty($fields);

        $attribute = $this->createProductAttribute(new DateType());
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
        $pimPath = 'Pim\Bundle\ProductBundle\Model\AttributeType\\';
        $oroPath = 'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\\';

        $numOfParams = 0;
        foreach ($this->config['attributes_config'] as $item) {
            $i = count($item['parameters']);
            $numOfParams = $i > $numOfParams ? $i : $numOfParams;
        }

        foreach ($attributeTypes as $type) {
            $type .= 'Type';
            $type = class_exists($pimPath . $type) ? $pimPath . $type : $oroPath . $type;

            $attribute = $this->createProductAttribute(new $type());
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
