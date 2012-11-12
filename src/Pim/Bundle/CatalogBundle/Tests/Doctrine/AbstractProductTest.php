<?php
namespace Pim\Bundle\CatalogBundle\Tests\Doctrine;

use \PHPUnit_Framework_TestCase;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;

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
    const SET_GROUP_INFO    = 'general';
    const SET_GROUP_MEDIA   = 'media';
    const SET_GROUP_SEO     = 'seo';
    const SET_GROUP_TECHNIC = 'technical';

    protected $objectManagerName = null;
    protected $productManager = null;
    protected $codeSet      = null;
    protected $codeAttributeSku  = null;
    protected $codeAttributeName = null;
    protected $codeAttributeSize = null;
    protected $productSku = null;

    /**
    * (non-documented)
    * TODO : Automatic link to PHPUnit_Framework_TestCase::setUp documentation
    */
    public function setUp()
    {
        parent::setUp();
        $objectManager = $this->container->get($this->objectManagerName);
        $this->productManager = new ProductManager($objectManager);

        $timestamp = str_replace('.', '', microtime(true));
        $this->codeSet      = 'set_'.$timestamp;
        $this->codeAttributeSku  = 'sku_'.$timestamp;
        $this->codeAttributeName = 'name_'.$timestamp;
        $this->codeAttributeSize = 'size_'.$timestamp;
        $this->productSku    = 'my_sku_'.$timestamp;

        // TODO : take a look on KernelAwareTest to avoid to drop data at any setUp + rename test method as testMethod ?
    }

    /**
     * test related method
     */
    public function testFlexibleProduct()
    {
        $this->createProductSet();

        $this->findProductSet();
        $this->findProductGroup();
        $this->findProductAttribute();

        $this->createProduct();

        $this->cloneSet();
    }

    /**
     * test related method(s)
     */
    public function createProductSet()
    {
        // create product type
        $set = $this->productManager->getNewSetInstance();
        $set->setCode($this->codeSet);
        $set->setTitle('My type title');
        $this->assertEquals($set->getCode(), $this->codeSet);

        // add groups
        $groups = array();
        $groupCodes = array(self::SET_GROUP_INFO, self::SET_GROUP_MEDIA, self::SET_GROUP_SEO, self::SET_GROUP_TECHNIC);
        foreach ($groupCodes as $code) {
            $group = $this->productManager->getNewGroupInstance();
            $group->setCode($code);
            $group->setTitle('Group '.$code);
            $set->addGroup($group);
        }
        $this->assertEquals($set->getGroups()->count(), count($groupCodes));
        $groupInfo = $set->getGroups()->first();
        $groupTechnic = $set->getGroups()->last();

        // check group getter / setter
        $this->assertEquals($groupInfo->getCode(), self::SET_GROUP_INFO);
        $this->assertEquals($groupInfo->getTitle(), 'Group '.self::SET_GROUP_INFO);

        // add a field sku
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode($this->codeAttributeSku);
        $title = 'Sku';
        $attribute->setTitle($title);
        $attribute->setType(BaseFieldFactory::FIELD_STRING);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(true);
        $attribute->setValueRequired(true);
        $attribute->setSearchable(false);
        $attribute->setTranslatable(false);
        $this->productManager->getPersistenceManager()->persist($attribute);
        $groupInfo->addAttribute($attribute);
        $this->assertEquals($groupInfo->getAttributes()->count(), 1);

        // check field getter / setter
        $this->assertEquals($attribute->getCode(), $this->codeAttributeSku);
        $this->assertEquals($attribute->getTitle(), $title);
        $this->assertEquals($attribute->getType(), BaseFieldFactory::FIELD_STRING);
        $this->assertEquals($attribute->getScope(), BaseFieldFactory::SCOPE_GLOBAL);
        $this->assertEquals($attribute->getUniqueValue(), true);
        $this->assertEquals($attribute->getValueRequired(), true);
        $this->assertEquals($attribute->getSearchable(), false);
        $this->assertEquals($attribute->getTranslatable(), false);

        // add a field name
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode($this->codeAttributeName);
        $attribute->setTitle('Name');
        $attribute->setType(BaseFieldFactory::FIELD_STRING);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(false);
        $attribute->setValueRequired(true);
        $attribute->setSearchable(true);
        $attribute->setTranslatable(false);
        $this->productManager->getPersistenceManager()->persist($attribute);
        $groupInfo->addAttribute($attribute);
        $this->assertEquals($groupInfo->getAttributes()->count(), 2);

        // add a field size
        $attribute = $this->productManager->getNewAttributeInstance();
        $attribute->setCode($this->codeAttributeSize);
        $attribute->setTitle('Size');
        $attribute->setType(BaseFieldFactory::FIELD_SELECT);
        $attribute->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $attribute->setUniqueValue(false);
        $attribute->setValueRequired(false);
        $attribute->setSearchable(false);
        $attribute->setTranslatable(false);
        $this->productManager->getPersistenceManager()->persist($attribute);
        $groupTechnic->addAttribute($attribute);
        $this->assertEquals($groupTechnic->getAttributes()->count(), 1);

        // add options
        $values = array('S', 'M', 'L', 'XL');
        $order = 1;
        foreach ($values as $value) {
            $option = $this->productManager->getNewAttributeOptionInstance();
            $option->setValue($order++);
            $option->setSortOrder(1);
            $attribute->addOption($option);
        }
        $this->assertEquals($attribute->getOptions()->count(), count($values));

        // persist
        $this->productManager->getPersistenceManager()->persist($set);
        $this->productManager->getPersistenceManager()->flush();

        // test ids
        $this->assertNotNull($set->getId());
        $this->assertNotNull($groupInfo->getId());
        $this->assertNotNull($attribute->getId());
    }

    /**
     * test related method(s)
     */
    public function findProductSet()
    {
        $set = $this->productManager->getSetRepository()->findOneByCode($this->codeSet);
        $class = $this->productManager->getSetClass();
        $this->assertTrue($set instanceof $class);
        $this->assertEquals($set->getCode(), $this->codeSet);
        $this->assertEquals($set->getGroups()->count(), 4);
    }

    /**
     * test related method(s)
     */
    public function findProductGroup()
    {
        // TODO
    }

    /**
     * test related method(s)
     */
    public function findProductAttribute()
    {
        $attribute = $this->productManager->getAttributeRepository()->findOneByCode($this->codeAttributeSku);
        $class = $this->productManager->getAttributeClass();
        $this->assertTrue($attribute instanceof $class);
        $this->assertEquals($attribute->getCode(), $this->codeAttributeSku);
    }

    /**
    * test related method(s)
    */
    public function createProduct()
    {
        // get product type
        $set = $this->productManager->getSetRepository()->findOneByCode($this->codeSet);

        // create product
        $product = $this->productManager->getNewEntityInstance();
        $product->setSet($set);

        // create value
        $attribute = $this->productManager->getAttributeRepository()->findOneByCode($this->codeAttributeSku);
        $value = $this->productManager->getNewAttributeValueInstance();
        $value->setAttribute($attribute);
        $value->setData($this->productSku);
        $product->addValue($value);

        // persist product
        $this->productManager->getPersistenceManager()->persist($product);
        $this->productManager->getPersistenceManager()->flush();
    }

    /**
     * test related method(s)
     */
    public function cloneSet()
    {
        // get product type
        $set = $this->productManager->getSetRepository()->findOneByCode($this->codeSet);

        // clone
        $clonedType = $this->productManager->cloneSet($set);
        // check
        $this->assertEquals($set->getCode(), $clonedType->getCode());
        $this->assertEquals($set->getGroups()->count(), $clonedType->getGroups()->count());
    }

}