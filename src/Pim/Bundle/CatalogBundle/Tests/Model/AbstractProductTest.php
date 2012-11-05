<?php
namespace Pim\Bundle\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use Pim\Bundle\CatalogBundle\Tests\Model\KernelAwareTest;

/**
 * Provide abstract test for product model (can be used for different implementation)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class AbtractProductTest extends KernelAwareTest
{
    const TYPE_BASE          = 'base_test';
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';
    const FIELD_SKU          = 'sku';
    const FIELD_NAME         = 'name';
    const FIELD_DESC         = 'description';
    const FIELD_SHORTDESC    = 'short_description';

    protected $productManagerName     = null;
    protected $productTypeManagerName = null;

    protected $productClass = null;
    protected $typeClass    = null;
    protected $fieldClass   = null;

    protected $newTypeCode  = null;
    protected $newProductId = null;

    /**
    * (non-documented)
    * TODO : Automatic link to PHPUnit_Framework_TestCase::setUp documentation
    */
    public function setUp()
    {
        parent::setUp();
        // create type and a product if not exists
        if (!$this->newTypeCode) {
            $this->newTypeCode = self::TYPE_BASE.'_'.time();
            $manager = $this->container->get($this->productTypeManagerName);
            $manager->create($this->newTypeCode);
            // add info fields
            $fields = array(self::FIELD_SKU, self::FIELD_NAME, self::FIELD_SHORTDESC, self::FIELD_DESC, 'color');
            foreach ($fields as $fieldCode) {
                $manager->addField($fieldCode, 'text', self::TYPE_GROUP_INFO);
            }
            // add media fields
            $fields = array('image', 'thumbnail');
            foreach ($fields as $fieldCode) {
                $manager->addField($fieldCode, 'text', self::TYPE_GROUP_MEDIA);
            }
            // add others empty groups
            $manager->addGroup(self::TYPE_GROUP_SEO);
            $manager->addGroup(self::TYPE_GROUP_TECHNIC);
            $manager->persist();
            $manager->flush();
            // create product
            $productManager = $manager->newProductInstance();
            $productManager->persist();
            $productManager->flush();
            $this->newProductId = $productManager->getObject()->getId();
        }
    }

    /**
     * test related method
     */
    public function testFind()
    {
        $manager = $this->container->get($this->productManagerName);
        $manager->find($this->newProductId);
        $this->assertInstanceOf($this->productClass, $manager->getObject());
        $this->assertEquals($manager->getObject()->getId(), $this->newProductId);
    }

    /**
     * test related method
     */
    public function testCreate()
    {
        $typeManager = $this->container->get($this->productTypeManagerName);
        $manager = $typeManager->newProductInstance();
        $manager->persist();
        $manager->flush();
        $this->assertInstanceOf($this->productClass, $manager->getObject());
    }

    /**
    * test basic getters / setters
    */
    public function testGettersSetters()
    {
        $manager = $this->container->get($this->productManagerName);
        $manager->find($this->newProductId);
        $sku = 'my sku';
        $name = 'my name';
        $color = 'green';
        $manager->setValue(self::FIELD_SKU, $sku);
        $manager->setValue(self::FIELD_NAME, $name);
        $manager->setColor($color);
        $manager->persist();
        $manager->flush();
        $this->assertEquals($sku, $manager->getValue(self::FIELD_SKU));
        $this->assertEquals($name, $manager->getValue(self::FIELD_NAME));
        $this->assertEquals($color, $manager->getColor());

        // TODO translate behaviour
    }

}