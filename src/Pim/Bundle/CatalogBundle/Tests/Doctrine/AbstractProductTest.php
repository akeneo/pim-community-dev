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
    protected $codeType      = null;
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

        $this->codeType      = 'sku_'.microtime();
        $this->codeFieldSku  = 'sku_'.microtime();
        $this->codeFieldName = 'name_'.microtime();
        $this->codeFieldSize = 'size_'.microtime();
        $this->productSku    = 'my-sku-'.microtime();

        // TODO : take a look on KernelAwareTest to avoid to drop data at any setUp + rename test method as testMethod
    }

    /**
     * test related method
     */
    public function testFlexibleProduct()
    {
        $this->createProductType();

        $this->findProductType();
        $this->findProductGroup();
        $this->findProductField();

        $this->createProduct();

        $this->cloneType();
/*
//        $persistenceManager->clear();

        // find type / check type
        $type2 = $this->productManager->getTypeRepository()->findOneByCode($newTypeCode);
        $this->assertEquals($type->getCode(), $newTypeCode);
        $this->assertEquals($type->getId(), $type2->getId());
        $this->assertEquals($type->getTitle(), $type2->getTitle());
//        $this->assertEquals(count($type2->getGroups()), 1);

        // check group
        $groups = $this->productManager->getGroupRepository()->findAll();
        $this->assertTrue($groups != null);
        $this->assertEquals($group->getCode(), self::TYPE_GROUP_INFO);
        $this->assertTrue($group->getId() != null);
        $this->assertEquals($group->getTitle(), 'Group');
        $this->assertEquals(count($group->getFields()), 1);
    //    $group->removeField($field);
     //   $this->assertEquals(count($group->getFields()), 0);

        // remove group
        $type->removeGroup($group);
//        $this->assertEquals(count($type->getGroups()), 0);

        // find field
        $field2 = $this->productManager->getFieldRepository()->findOneByCode($newFieldCode);
        $this->assertEquals($field2->getCode(), $newFieldCode);
        $this->assertEquals($field2->getTitle(), 'Field');
        $this->assertEquals($field2->getId(), $field->getId());
        $this->assertEquals($field2->getType(), $field->getType());
        $this->assertEquals($field2->getUniqueValue(), $field->getUniqueValue());
        $this->assertEquals($field2->getValueRequired(), $field->getValueRequired());
        $this->assertEquals($field2->getSearchable(), $field->getSearchable());
//        $this->assertEquals($field2->getScope(), $field->getScope());

        // create product
        $class = $this->productManager->getEntityClass();
        $product = new $class;
        $product->setType($type);

        // create value
        $class = $this->productManager->getValueClass();
        $value = new $class;
        $value->setField($field);
        $value->setData('My value data');
        $product->addValue($value);


        // persist product

        $persistenceManager->persist($product);
        $persistenceManager->flush();
*/
    }

    /**
     * test related method(s)
     */
    public function createProductType()
    {
        // create product type
        $type = $this->productManager->getNewTypeInstance();
        $type->setCode($this->codeType);
        $type->setTitle('My type title');
        $this->assertEquals($type->getCode(), $this->codeType);

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
        $field = $this->productManager->getNewFieldInstance();
        $field->setCode($this->codeFieldSku);
        $title = 'My title '.$this->codeFieldSku;
        $field->setTitle($title);
        $field->setType(BaseFieldFactory::FIELD_STRING);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(true);
        $field->setValueRequired(true);
        $field->setSearchable(false);
        $groupInfo->addField($field);
        $this->assertEquals($groupInfo->getFields()->count(), 1);

        // check field getter / setter
        $this->assertEquals($field->getCode(), $this->codeFieldSku);
        $this->assertEquals($field->getTitle(), $title);
        $this->assertEquals($field->getType(), BaseFieldFactory::FIELD_STRING);
        $this->assertEquals($field->getScope(), BaseFieldFactory::SCOPE_GLOBAL);
        $this->assertEquals($field->getUniqueValue(), true);
        $this->assertEquals($field->getValueRequired(), true);
        $this->assertEquals($field->getSearchable(), false);

        // add a field name
        $field = $this->productManager->getNewFieldInstance();
        $field->setCode($this->codeFieldName);
        $field->setTitle('My title '.$this->codeFieldName);
        $field->setType(BaseFieldFactory::FIELD_STRING);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(false);
        $field->setValueRequired(true);
        $field->setSearchable(true);
        $groupInfo->addField($field);
        $this->assertEquals($groupInfo->getFields()->count(), 2);

        // add a field size
        $field = $this->productManager->getNewFieldInstance();
        $field->setCode($this->codeFieldSize);
        $field->setTitle('My title '.$this->codeFieldSize);
        $field->setType(BaseFieldFactory::FIELD_SELECT);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);
        $field->setUniqueValue(false);
        $field->setValueRequired(false);
        $field->setSearchable(false);
        // TODO : test options !
        $groupTechnic->addField($field);
        $this->assertEquals($groupTechnic->getFields()->count(), 1);

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
    public function findProductType()
    {
        $type = $this->productManager->getTypeRepository()->findOneByCode($this->codeType);
        $class = $this->productManager->getTypeClass();
        $this->assertTrue($type instanceof $class);
        $this->assertEquals($type->getCode(), $this->codeType);
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
    public function findProductField()
    {
        $field = $this->productManager->getFieldRepository()->findOneByCode($this->codeFieldSku);
        $class = $this->productManager->getFieldClass();
        $this->assertTrue($field instanceof $class);
        $this->assertEquals($field->getCode(), $this->codeFieldSku);
    }

    /**
    * test related method(s)
    */
    public function createProduct()
    {
        // get product type
        $type = $this->productManager->getTypeRepository()->findOneByCode($this->codeType);

        // create product
        $product = $this->productManager->getNewEntityInstance();
        $product->setType($type);

        // create value
        $field = $this->productManager->getFieldRepository()->findOneByCode($this->codeFieldSku);
        $value = $this->productManager->getNewValueInstance();
        $value->setField($field);
        $value->setData($this->productSku);
        $product->addValue($value);

        // persist product
        $this->productManager->getPersistenceManager()->persist($product);
        $this->productManager->getPersistenceManager()->flush();
    }

    /**
     * test related method(s)
     */
    public function cloneType()
    {
        // get product type
        $type = $this->productManager->getTypeRepository()->findOneByCode($this->codeType);

        // clone
        $clonedType = $this->productManager->cloneType($type);
        // check
        $this->assertEquals($type->getCode(), $clonedType->getCode());
        $this->assertEquals($type->getGroups()->count(), $clonedType->getGroups()->count());
    }

}