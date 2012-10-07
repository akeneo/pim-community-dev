<?php
namespace Akeneo\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;

/**
 *
 * Aims to test product type model
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeTest extends KernelAwareTest
{
    const TYPE_BASE          = 'base_test';
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';

    /**
     * Enter description here ...
     */
    public function testCreate()
    {
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type->create(self::TYPE_BASE);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Model\ProductType', $type);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Entity\Product\Type', $type->getObject());
        $this->assertEquals($type->getCode(), self::TYPE_BASE);
    }

    /**
     * Enter description here ...
     */
    public function testPersistence()
    {
        // create
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type->create(self::TYPE_BASE);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Model\ProductType', $type);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Entity\Product\Type', $type->getObject());
        $this->assertEquals($type->getCode(), self::TYPE_BASE);
        // add info fields
        $fields = array('sku', 'name', 'short_description', 'description', 'color');
        foreach ($fields as $fieldCode) {
            if (!$type->getField($fieldCode)) {
                $type->addField($fieldCode, 'text', self::TYPE_GROUP_INFO);
            }
        }
        // add media fields
        $fields = array('image', 'thumbnail');
        foreach ($fields as $fieldCode) {
            if (!$type->getField($fieldCode)) {
                $type->addField($fieldCode, 'text', self::TYPE_GROUP_MEDIA);
            }
        }
        // add others empty groups
        $type->addGroup(self::TYPE_GROUP_SEO);
        $type->addGroup(self::TYPE_GROUP_TECHNIC);

        // persist type
        $type->persist();
        $type->flush();
        // asserts
        $this->assertEquals(count($type->getGroupsCodes()), 4);
        $this->assertEquals(count($type->getFieldsCodes()), 7);

        // find
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type->find(self::TYPE_BASE);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Model\ProductType', $type);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Entity\Product\Type', $type->getObject());
        $this->assertEquals($type->getCode(), self::TYPE_BASE);

        // test accessor
        $group = $type->getGroup(self::TYPE_GROUP_SEO);
        $this->assertInstanceOf('Akeneo\CatalogBundle\Entity\Product\Group', $group);
        $field = $type->getField('sku');
        $this->assertInstanceOf('Akeneo\CatalogBundle\Entity\Product\Field', $field);

        // remove related entity
        $group = $type->removeGroup(self::TYPE_GROUP_SEO);
        $this->assertEquals(count($type->getGroupsCodes()), 3);
        $group = $type->removeFieldFromType('short_description');
        $this->assertEquals(count($type->getFieldsCodes()), 6);
        $group = $type->removeField('description');
        $this->assertEquals(count($type->getFieldsCodes()), 5);

        // create product and related service
        $product = $type->newProductInstance();
        $this->assertInstanceOf('Akeneo\CatalogBundle\Model\Product', $product);

        // remove
        $type->remove();
    }

}