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

        $this->manager = static::$kernel->getContainer()->get('product_manager');
        $this->localeManager = static::$kernel->getContainer()->get('pim_config.manager.locale');
        $this->factory = static::$kernel->getContainer()
            ->get('oro_flexibleentity.attributetype.factory');

        $this->service = new AttributeService($this->manager, $this->localeManager, $this->factory);
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
     * Test createAttributeFromFormData method
     */
    public function testCreateAttributeFromFormData()
    {
        $data = array('attributeType' => 'pim_product_metric');
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
