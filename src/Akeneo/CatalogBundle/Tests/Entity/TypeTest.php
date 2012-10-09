<?php
namespace Akeneo\CatalogBundle\Tests\Entity;

use Akeneo\CatalogBundle\Entity\ProductType;
use Akeneo\CatalogBundle\Entity\ProductField;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TypeTest extends AbstractTest
{
    /**
     * @var string
     */
    const PT_CODE_CHANGED_VALUE = 'azerty';

    /**
     * (non-documented)
     * @see EntityTest
     */
    protected function getEntityClassName()
    {
        return 'Akeneo\CatalogBundle\Entity\ProductType';
    }

    /**
     * (non-documented)
     */
    public function setUp()
    {
        parent::setUp();

        // TODO : set values to default entity
//         $this->entity->setId(1);
//         $this->entity->setCode('pouic');
    }

    /**
     * test id attribute getter
     */
    public function testGetId()
    {
        $this->assertNull($this->entity->getId());
    }

    /**
     * Test code attribute accessors
     */
    public function testCodeAccessors()
    {
        $this->assertNull($this->entity->getCode());
        $this->entity->setCode(self::PT_CODE_CHANGED_VALUE);
        $this->assertEquals(self::PT_CODE_CHANGED_VALUE, $this->entity->getCode());
    }

    /**
     * Test product accessors (add, remove, get)
     *
    public function testFieldAccessors()
    {
        $this->assertGetFields(0);

        // add product
        $firstField = $this->createField();
        $this->entity->addField($firstField);
        $this->assertGetFields(1);

        // add product
        $secondField = $this->createField();
        $this->entity->addField($secondField);
        $this->assertGetFields(2);

        // verify first product is different of the second
        $productList = $this->entity->getFields();
        $this->assertNotSame($productList->first(), $productList->last());

        // remove product
        $this->entity->removeField($firstField);
        $this->assertGetFields(1);

        // verify first product is deleted and second already exists
        $productList = $this->entity->getFields();
        $this->assertSame($productList->first(), $productList->last());
        $this->assertSame($productList->first(), $secondField);
        $this->assertNotSame($productList->first(), $firstField);
    }*/

    /**
     * Assert count of product list
     * @param integer $count
     *
    protected function assertGetFields($count)
    {
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->entity->getFields());
        $this->assertCount($count, $this->entity->getFields()->toArray());
    }*/

    /**
     * Remove a product non existant to the entity
     *
    public function testRemoveNonExistantField()
    {
        $this->assertGetFields(0);
        // TODO ! access to first product.. -> exception ??! => must be tested

        // add product
        $firstField = $this->createField();
        $this->entity->addField($firstField);
        $this->assertGetFields(1);

        // remove non-existant product
        $secondField = $this->createField();
        $this->entity->removeField($secondField);

        // assert count is equal
        $this->assertGetFields(1);
    }*/

    /**
     * Remove a product already removed to the entity
     *
    public function testRemoveAlreadyRemovedField()
    {
        $this->assertGetFields(0);

        // add product
        $firstField = $this->createField();
        $this->entity->addField($firstField);
        $this->assertGetFields(1);

        // remove product
        $this->entity->removeField($firstField);
        $this->assertGetFields(0);

        // remove already removed product
        $this->entity->removeField($firstField);
        $this->assertGetFields(0);
    }*/

    /**
     * Add a product already added to the entity
     *
    public function testAddAlreadyAddedField()
    {
        $this->assertGetFields(0);

        // create product
        $firstField = $this->createField();

        // add product
        $this->entity->addField($firstField);
        $this->assertGetFields(1);

        // add already added product
        $this->entity->addField($firstField);
        $this->assertGetFields(2);

        // remove product
        $this->entity->removeField($firstField);
        $this->assertGetFields(1);
    }*/

    /**
     * Create an empty product entity
     * @return \Akeneo\CatalogBundle\Entity\Product\Field
     */
    protected function createField()
    {
        return new Field();
    }
}