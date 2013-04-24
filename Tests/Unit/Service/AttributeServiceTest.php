<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Service;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
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
     * @var array Measures
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
     * Create a product attribute entity
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute()
    {
        $productAttribute = new ProductAttribute();

        $attribute = new Attribute();
        $productAttribute->setAttribute($attribute);

        $productAttribute->setAttributeType(AbstractAttributeType::TYPE_METRIC_CLASS);

        return $productAttribute;
    }

    /**
     * Test getInitialFields method
     */
    public function testGetInitialFields()
    {
        $fields = $this->service->getInitialFields();
        $this->assertNotEmpty($fields);
        foreach ($fields as $field) {
            $this->assertArrayHasKey('name', $field);
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
}
