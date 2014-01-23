<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductTransformerTest extends ORMTransformerTestCase
{
    protected $productManager;
    protected $attributeCache;
    protected $attributes;
    protected $transformer;
    protected $associationsReader;
    protected $fields;
    protected $product;
    protected $values;

    protected function setUp()
    {
        parent::setUp();
        $this->fields = [];
        $this->values = [];
        $this->attributes = [];

        $this->metadata->expects($this->any())
            ->method('hasField')
            ->will(
                $this->returnCallback(
                    function ($propertyPath) {
                        return in_array($propertyPath, $this->fields);
                    }
                )
            );

        $this->product = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\Product')
            ->setMethods(['getValue', 'createValue', 'addValue'])
            ->getMock();

        $this->product->expects($this->any())
            ->method('createValue')
            ->will($this->returnCallback([$this, 'createValue']));

        $this->productManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productManager->expects($this->any())
            ->method('createProduct')
            ->will($this->returnValue($this->product));

        $this->productRepository = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productManager->expects($this->any())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($this->productRepository));

        $this->attributeCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\AttributeCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCache->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnCallback([$this, 'getAttributes']));

        $this->associationsReader = $this->getMock('Pim\Bundle\ImportExportBundle\Reader\CachedReader');

        $this->addAttribute('identifier', ORMProductTransformer::IDENTIFIER_ATTRIBUTE_TYPE);
        $this->addColumn('identifier', true, false);

        $this->transformer = new ORMProductTransformer(
            $this->doctrine,
            $this->propertyAccessor,
            $this->guesser,
            $this->columnInfoTransformer,
            $this->productManager,
            $this->attributeCache,
            $this->associationsReader
        );
    }

    public function testTransform()
    {
        $this->addAttribute('col2');
        $this->addColumn('col1');
        $this->addColumn('col2', true, false);
        $product = $this->transformer->transform(
            'Pim\Bundle\CatalogBundle\Model\Product',
            [
                'identifier' => 'id',
                'col1' => 'value1',
                'col2' => 'value2'
            ]
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
        $column->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($label));
        if ($addField) {
            $this->fields[] = $column->getPropertyPath();
        } else {
            $column
                ->expects($this->once())
                ->method('setAttribute')
                ->with($this->equalTo($this->attributes[$label]));
        }
    }

    protected function addAttribute($code, $attributeType = 'type')
    {
        $this->attributes[$code] = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $this->attributes[$code]
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));
        $this->attributes[$code]
            ->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeType));

        return $this->attributes[$code];
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function createValue($name)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');
        $this->values[] = $value;

        return $value;
    }
}
