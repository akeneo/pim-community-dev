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
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';

    protected $objectManagerName = null;
    protected $productManager = null;
    protected $codeSet      = null;
    protected $codeFieldSku  = null;
    protected $codeFieldName = null;
    protected $codeFieldSize = null;
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
        $this->codeFieldSku  = 'sku_'.$timestamp;
        $this->codeFieldName = 'name_'.$timestamp;
        $this->codeFieldSize = 'size_'.$timestamp;
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
        $type = $this->productManager->getNewSetInstance();
        $type->setCode($this->codeSet);
        $type->setTitle('My type title');
        $this->assertEquals($type->getCode(), $this->codeSet);

        // add groups
        $groups = array();
        $groupCodes = array(self::TYPE_GROUP_INFO, self::TYPE_GROUP_MEDIA, self::TYPE_GROUP_SEO, self::TYPE_GROUP_TECHNIC);
        foreach ($groupCodes as $code) {
            $group = $this->productManager->getNewGroupInstance();
            $group->setCode($code);
            $group->setTitle('Group '.$code);
            $type->addGroup($group);
        }
        $this->assertEquals($type->getGroups()->count(), count($groupCodes));
        $groupInfo = $type->getGroups()->first();
        $groupTechnic = $type->getGroups()->last();

        // check group getter / setter
        $this->assertEquals($groupInfo->getCode(), self::TYPE_GROUP_INFO);
        $this->assertEquals($groupInfo->getTitle(), 'Group '.self::TYPE_GROUP_INFO);

        // add a field sku
        $field = $this->productManager->getNewAttributeInstance();
        $field->setCode($this->codeFieldSku);
        $title = 'My title '.$this->codeFieldSku;
        $field->setTitle($title);
        $field->setType(BaseFieldFactory::FIELD_STRING);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(true);
        $field->setValueRequired(true);
        $field->setSearchable(false);
        $this->productManager->getPersistenceManager()->persist($field);
        $groupInfo->addAttribute($field);
        $this->assertEquals($groupInfo->getAttributes()->count(), 1);

        // check field getter / setter
        $this->assertEquals($field->getCode(), $this->codeFieldSku);
        $this->assertEquals($field->getTitle(), $title);
        $this->assertEquals($field->getType(), BaseFieldFactory::FIELD_STRING);
        $this->assertEquals($field->getScope(), BaseFieldFactory::SCOPE_GLOBAL);
        $this->assertEquals($field->getUniqueValue(), true);
        $this->assertEquals($field->getValueRequired(), true);
        $this->assertEquals($field->getSearchable(), false);

        // add a field name
        $field = $this->productManager->getNewAttributeInstance();
        $field->setCode($this->codeFieldName);
        $field->setTitle('My title '.$this->codeFieldName);
        $field->setType(BaseFieldFactory::FIELD_STRING);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(false);
        $field->setValueRequired(true);
        $field->setSearchable(true);
        $this->productManager->getPersistenceManager()->persist($field);
        $groupInfo->addAttribute($field);
        $this->assertEquals($groupInfo->getAttributes()->count(), 2);

        // add a field size
        $field = $this->productManager->getNewAttributeInstance();
        $field->setCode($this->codeFieldSize);
        $field->setTitle('My title '.$this->codeFieldSize);
        $field->setType(BaseFieldFactory::FIELD_SELECT);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(false);
        $field->setValueRequired(false);
        $field->setSearchable(false);
        $this->productManager->getPersistenceManager()->persist($field);
        $groupTechnic->addAttribute($field);
        $this->assertEquals($groupTechnic->getAttributes()->count(), 1);

        // add options
        $values = array('S', 'M', 'L', 'XL');
        $order = 1;
        foreach ($values as $value) {
            $option = $this->productManager->getNewAttributeOptionInstance();
            $option->setValue($order++);
            $option->setSortOrder(1);
            $field->addOption($option);
        }
        $this->assertEquals($field->getOptions()->count(), count($values));

        // persist
        $this->productManager->getPersistenceManager()->persist($type);
        $this->productManager->getPersistenceManager()->flush();

        // test ids
        $this->assertNotNull($type->getId());
        $this->assertNotNull($groupInfo->getId());
        $this->assertNotNull($field->getId());
    }

    /**
     * test related method(s)
     */
    public function findProductSet()
    {
        $type = $this->productManager->getSetRepository()->findOneByCode($this->codeSet);
        $class = $this->productManager->getSetClass();
        $this->assertTrue($type instanceof $class);
        $this->assertEquals($type->getCode(), $this->codeSet);
        $this->assertEquals($type->getGroups()->count(), 4);
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
        $field = $this->productManager->getAttributeRepository()->findOneByCode($this->codeFieldSku);
        $class = $this->productManager->getAttributeClass();
        $this->assertTrue($field instanceof $class);
        $this->assertEquals($field->getCode(), $this->codeFieldSku);
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
        $field = $this->productManager->getAttributeRepository()->findOneByCode($this->codeFieldSku);
        $value = $this->productManager->getNewAttributeValueInstance();
        $value->setAttribute($field);
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
        $type = $this->productManager->getSetRepository()->findOneByCode($this->codeSet);

        // clone
        $clonedType = $this->productManager->cloneSet($type);
        // check
        $this->assertEquals($type->getCode(), $clonedType->getCode());
        $this->assertEquals($type->getGroups()->count(), $clonedType->getGroups()->count());
    }

}