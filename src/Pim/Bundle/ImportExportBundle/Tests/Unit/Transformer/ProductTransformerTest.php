<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\ProductTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTransformerTest extends EntityTransformerTestCase
{
    protected $productManager;
    protected $flexibleRepository;
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
        $this->fields = array();
        $this->values = array();
        $this->attributes = array();

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
            ->setMockClassName('product_class')
            ->getMock();

        $this->product->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue('id'));
        $this->product->expects($this->any())
            ->method('createValue')
            ->will($this->returnCallback(array($this, 'createValue')));

        $this->flexibleRepository = $this->getMock(
            'Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface'
        );
        $this->productManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productManager->expects($this->any())
            ->method('createProduct')
            ->will($this->returnValue($this->product));
        $this->productManager->expects($this->any())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($this->flexibleRepository));
        $this->productManager->expects($this->any())
            ->method('getFlexibleValueName')
            ->will($this->returnValue('product_value_class'));

        $this->attributeCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\AttributeCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCache->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnCallback(array($this, 'getAttributes')));

        $this->attributeCache->expects($this->any())
            ->method('getRequiredAttributeCodes')
            ->will($this->returnValue(array('required')));

        $this->associationsReader = $this->getMock('Pim\Bundle\BaseConnectorBundle\Reader\CachedReader');

        $this->addAttribute('identifier', ProductTransformer::IDENTIFIER_ATTRIBUTE_TYPE);
        $this->addColumn('identifier', true, false);

        $this->transformer = new ProductTransformer(
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
        $this->addAttribute('required');
        $this->addAttribute('skip');
        $this->addColumn('col1');
        $this->addColumn('col2', true, false);
        $this->addColumn('required', true, false);
        $this->addColumn('skip', true, false, true);

        $product = $this->transformer->transform(
            'product_class',
            array(
                'identifier' => 'id',
                'col1' => 'value1',
                'col2' => 'value2',
                'skip' => 'skip',
                'required' => ''
            )
        );

        $this->assertEquals('col1_path-value1', $product->col1_path);
        $this->assertEquals('identifier_path-id', $this->values[0]->identifier_path);
        $this->assertEquals('col2_path-value2', $this->values[1]->col2_path);
        $this->assertEmpty($this->transformer->getErrors('product_class'));
        $this->assertCount(4, $this->transformer->getTransformedColumnsInfo('product_class'));

        $product2 = $this->transformer->transform(
            'product_class',
            array(
                'identifier' => 'id2',
                'col1' => 'value3',
                'col2' => 'value4',
                'skip' => 'skip',
                'required' => ''
            )
        );

        $this->assertEquals('col1_path-value3', $product2->col1_path);
        $this->assertEmpty($this->transformer->getErrors('product_class'));

        $this->transformer->reset();

        $product3 = $this->transformer->transform(
            'product_class',
            array(
                'identifier' => 'id3',
                'col1' => 'value5',
                'col2' => 'value6',
                'skip' => 'skip',
                'required' => ''
            )
        );

        $this->assertEquals('col1_path-value5', $product3->col1_path);
        $this->assertEmpty($this->transformer->getErrors('product_class'));
    }

    public function testFailingTransform()
    {
        $this->addAttribute('col2');
        $this->addColumn('col1', false);
        $this->addColumn('col2', false, false);
        $this->addTransformer('col1_path', true);
        $this->addTransformer('col2_path', true);

        $this->transformer->transform(
            'product_class',
            array('identifier' => 'id', 'col1' => 'val1', 'col2' => 'val2')
        );
        $this->assertEquals(
            array(
                'col1' => array(
                    array(
                        'error_message',
                        array('error_parameters')
                    )
                ),
                'col2' => array(
                    array(
                        'error_message',
                        array('error_parameters')
                    )
                )
            ),
            $this->transformer->getErrors('product_class')
        );
    }

    public function testTransformWithAssociations()
    {
        $this->addAttribute('col2');
        $this->addColumn('col1');
        $this->addColumn('col2', true, false);
        $this->addColumn('association1', false, true, false, false, array('products'));
        $this->addColumn('association1', false, true, false, false, array('groups'));
        $this->addColumn('association2', false, true, false, false, array('products'));
        $this->associationsReader
            ->expects($this->at(0))
            ->method('addItem')
            ->with(
                $this->equalTo(
                    array(
                        'owner' => 'id',
                        'associationType' => 'association1',
                        'products'        => '1,2,3',
                        'groups'          => '1,2'
                    )
                )
            );
        $this->associationsReader
            ->expects($this->at(1))
            ->method('addItem')
            ->with(
                $this->equalTo(
                    array(
                        'owner' => 'id',
                        'associationType' => 'association2',
                        'products'        => '4,5'
                    )
                )
            );

        $product = $this->transformer->transform(
            'product_class',
            array(
                'identifier' => 'id',
                'col1' => 'value1',
                'col2' => 'value2',
                'association1_products' => '1,2,3',
                'association1_groups'   => '1,2',
                'association2_products' => '4,5'
            )
        );

        $this->assertEquals('col1_path-value1', $product->col1_path);
        $this->assertEquals('identifier_path-id', $this->values[0]->identifier_path);
        $this->assertEquals('col2_path-value2', $this->values[1]->col2_path);
        $this->assertEmpty($this->transformer->getErrors('product_class'));
        $this->assertCount(3, $this->transformer->getTransformedColumnsInfo('product_class'));
    }

    protected function addColumn(
        $name,
        $addTransformer = true,
        $addField = true,
        $skip = false,
        $failing = false,
        $suffixes = array()
    ) {
        $label = implode('_', array_merge(array($name), $suffixes));
        $column = parent::addColumn($label, $addTransformer, $skip, $failing, $suffixes);
        $column->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        if ($addField) {
            $this->fields[] = $column->getPropertyPath();
        } else {
            $column
                ->expects($this->any())
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

    public function createValue()
    {
        $value = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValue')
            ->setMockClassName('product_value_class')
            ->getMock();
        $this->values[] = $value;

        return $value;
    }
}
