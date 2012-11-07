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
    const TYPE_BASE          = 'base_test';
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';
    const FIELD_BASE         = 'fieldcode';

    protected $objectManagerName = null;
    protected $productManager = null;

    protected $modelType   = null;
    protected $modelEntity = null;

    protected $newTypeCode = null;

    /**
    * (non-documented)
    * TODO : Automatic link to PHPUnit_Framework_TestCase::setUp documentation
    */
    public function setUp()
    {
        parent::setUp();
        $objectManager = $this->container->get($this->objectManagerName);
        $this->productManager = new ProductManager($objectManager);
    }

    /**
     * test related method
     */
    public function testProductType()
    {
        // create product type
        $newTypeCode = self::TYPE_BASE.'_'.time();
        $manager = $this->productManager->getPersistenceManager();
        $class = $this->productManager->getTypeClass();
        $type = new $class;
        $type->setCode($newTypeCode);
        $type->setTitle('My title');
        $this->assertEquals($type->getCode(), $newTypeCode);

        // add a field
        $newFieldCode = self::FIELD_BASE.'_'.time();
        $class = $this->productManager->getFieldClass();
        $field = new $class();
        $field->setCode($newFieldCode);
        $field->setTitle('Field');
        $field->setType(BaseFieldFactory::FIELD_STRING);
        $field->setUniqueValue(false);
        $field->setValueRequired(false);
        $field->setSearchable(false);
        $field->setScope(BaseFieldFactory::SCOPE_GLOBAL);

        // add a group
        $class = $this->productManager->getGroupClass();
        $group = new $class();
        $group->setCode(self::TYPE_GROUP_INFO);
        $group->setTitle('Group');
        $group->addField($field);
        $type->addGroup($group);

        // persist
        $manager->persist($type);
        $manager->flush();
        $manager->clear();

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
        $group->removeField($field);
        $this->assertEquals(count($group->getFields()), 0);

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
    }

}