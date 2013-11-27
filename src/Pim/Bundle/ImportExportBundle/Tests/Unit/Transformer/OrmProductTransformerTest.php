<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\OrmProductTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductTransformerTest extends OrmTransformerTestCase
{
    protected $productManager;
    protected $attributeCache;
    protected $identifierAttribute;
    protected $transformer;
    protected $fields;
    protected $product;
    protected $values;

    protected function setUp()
    {
        parent::setUp();
        $this->fields = array();
        $this->values = array();

        $this->metadata->expects($this->any())
            ->method('hasField')
            ->will(
                $this->returnCallback(
                    function ($propertyPath) {
                        return in_array($propertyPath, $this->fields);
                    }
                )
            );

        $this->product = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductInterface')
            ->setMethods(array('getValue', 'createValue', 'addValue'))
            ->getMock();

        $this->product->expects($this->any())
            ->method('createValue')
            ->will($this->returnCallback(array($this, 'createValue')));

        $this->productManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productManager->expects($this->any())
            ->method('createProduct')
            ->will($this->returnValue($this->product));

        $this->attributeCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\AttributeCache')
            ->disableOriginalConstructor()
            ->getMock();

        $this->identifierAttribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
        $this->identifierAttribute
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('identifier'));
        $this->attributeCache->expects($this->any())
            ->method('getIdentifierAttribute')
            ->will($this->returnValue($this->identifierAttribute));

        $this->transformer = new OrmProductTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->labelTransformer,
            $this->productManager,
            $this->attributeCache
        );
        $this->addColumn('identifier', true, false);
    }

    public function testTransform()
    {
        $this->addColumn('col1');
        $this->addColumn('col2', true, false);
        $product = $this->transformer->transform(
            array(
                'identifier' => 'id',
                'col1' => 'value1',
                'col2' => 'value2'
            )
        );

        $this->assertEquals('col1_path-value1', $product->col1_path);
        $this->assertEquals('identifier_path-id', $this->values[0]->identifier_path);
        $this->assertEquals('col2_path-value2', $this->values[1]->col2_path);
        $this->assertEmpty($this->transformer->getErrors());
        $this->assertCount(3, $this->transformer->getTransformedColumnsInfo());
    }

    protected function addColumn($label, $addTransformer = true, $addField = true)
    {
        $column = parent::addColumn($label, $addTransformer);
        if ($addField) {
            $this->fields[] = $column->getPropertyPath();
        }
    }

    public function createValue($name)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValueInterface');
        $this->values[] = $value;

        return $value;
    }
}
