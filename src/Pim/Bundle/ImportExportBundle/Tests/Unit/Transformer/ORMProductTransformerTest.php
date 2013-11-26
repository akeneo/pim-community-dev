<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $productValues;
    protected $productManager;
    protected $transformer;
    protected $productValidator;
    protected $attributeCache;
    protected $propertyAccessor;
    protected $product;
    protected $attributes;
    protected $propertyErrors;
    protected $valueErrors;
    protected $columns;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productValues = array();
        $this->product = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductInterface')
            ->setMethods(array('getValue', 'addValue', 'createValue'))
            ->getMock();
        $this->product->expects($this->any())
            ->method('createValue')
            ->will($this->returnCallback(array($this, 'getProductValueMock')));
        $this->product->expects($this->any())
            ->method('addValue')
            ->will($this->returnCallback(array($this, 'addProductValue')));

        $this->productManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productManager->expects($this->any())
            ->method('createProduct')
            ->will($this->returnValue($this->product));

        $this->propertyErrors = array();
        $this->valueErrors = array();
        $this->productValidator = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productValidator->expects($this->any())
            ->method('validateProductProperties')
            ->will($this->returnCallback(array($this, 'getPropertyErrors')));
        $this->productValidator->expects($this->any())
            ->method('validateProductValue')
            ->will($this->returnCallback(array($this, 'getValueErrors')));

        $this->attributes = array();
        $this->columns = array();
        $this->addAttributeColumn('sku', 'identifier');
        $this->attributeCache = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\AttributeCache')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCache->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnCallback(array($this, 'getAttributes')));
        $this->attributeCache->expects($this->any())
            ->method('getColumns')
            ->will($this->returnCallback(array($this, 'getColumns')));
        $this->attributeCache->expects($this->any())
            ->method('getIdentifierAttribute')
            ->will($this->returnValue($this->attributes['sku']));

        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->propertyAccessor->expects($this->any())
            ->method('setValue')
            ->will($this->returnCallback(array($this, 'setProductValue')));

        $this->transformer = new ORMProductTransformer(
            $this->productManager,
            $this->productValidator,
            $this->attributeCache,
            $this->propertyAccessor
        );

        $this->addProductValueTransformer('default');
    }

    /**
     * Test related method
     */
    public function testPropertyTransformers()
    {
        $this->addPropertyTransformer('field1');
        $this->addPropertyTransformer('field2');

        $product = $this->transformer->getProduct(
            array(
                'sku'    => 'sku',
                'field1' => 'value1',
                'field2' => 'value2'
            )
        );

        $this->assertEquals('field1-value1', $product->field1);
        $this->assertEquals('field2-value2', $product->field2);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testFailingPropertyTransformer()
    {
        $this->addPropertyTransformer('field1');
        $this->addPropertyTransformer('field2', true);

        $this->transformer->getProduct(
            array(
                'sku'    => 'sku',
                'field1' => 'value1',
                'field2' => 'value2'
            )
        );
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testInvalidProperty()
    {
        $this->addPropertyTransformer('field1');
        $this->addPropertyTransformer('field2');

        $this->propertyErrors[] = 'property: error';

        $this->transformer->getProduct(
            array(
                'sku'    => 'sku',
                'field1' => 'value1',
                'field2' => 'value2'
            )
        );
    }

    /**
     * Test related method
     */
    public function testProductValues()
    {
        $this->addProductValueTransformer('transformed');
        $this->addAttributeColumn('key1', 'raw');
        $this->addAttributeColumn('key2', 'transformed');

        $this->transformer->getProduct(
            array(
                'sku'   => 'sku',
                'key1'  => 'value1',
                'key2'  => 'value2'
            )
        );
        $this->assertEquals($this->productValues['key1']->data, 'default-value1');
        $this->assertEquals($this->productValues['key2']->data, 'transformed-value2');
    }

    /**
     * Test related method
     */
    public function testEmptyProductValues()
    {
        $this->attributeCache
            ->expects($this->any())
            ->method('getRequiredAttributeCodes')
            ->will($this->returnValue(array('key1')));

        $this->addAttributeColumn('key1', 'raw');
        $this->addAttributeColumn('key2', 'raw');

        $this->transformer->getProduct(
            array(
                'sku'   => 'sku',
                'key1'  => '',
                'key2'  => ''
            )
        );

        $this->assertEquals($this->productValues['key1']->data, 'default-');
        $this->assertArrayNotHasKey('key2', $this->productValues);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testInvalidProductValue()
    {
        $this->addAttributeColumn('key1', 'raw');
        $this->valueErrors['key1'] = array('error');
        $this->transformer->getProduct(
            array(
                'sku'   => 'sku',
                'key1'  => 'test'
            )
        );
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testFailingProductValueTransformer()
    {
        $this->addAttributeColumn('attribute1', 'failing');
        $this->addProductValueTransformer('failing', true);
        $this->transformer->getProduct(
            array(
                'sku'           => 'sku',
                'attribute1'    => 'value1',
            )
        );
    }

    /**
     * @param ProductInterface $product
     * @param string           $name
     * @param mixed            $value
     */
    public function setProductValue($product, $name, $value)
    {
        $product->$name = $value;
    }

    /**
     * @param string  $propertyPath
     * @param boolean $failing
     *
     * @return \Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface
     */
    protected function addPropertyTransformer($propertyPath, $failing = false)
    {
        $transformer = $this->getPropertyTransformerMock($propertyPath, $failing);
        $this->transformer->addPropertyTransformer($propertyPath, $transformer);

        return $transformer;
    }

    /**
     * @param string  $backendType
     * @param boolean $failing
     *
     * @return \Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface
     */
    protected function addProductValueTransformer($backendType, $failing = false)
    {
        $transformer = $this->getPropertyTransformerMock($backendType, $failing);
        $this->transformer->addAttributeTransformer($backendType, $transformer);

        return $transformer;
    }

    /**
     * @param string  $prefix
     * @param boolean $failing
     *
     * @return \Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface
     */
    protected function getPropertyTransformerMock($prefix, $failing = false)
    {
        $transformer = $this->getMock(
            'Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface'
        );
        if ($failing) {
            $transformer->expects($this->any())
                ->method('transform')
                ->will($this->throwException(new InvalidValueException('ERROR', array('prefix'=>$prefix))));
        } else {
            $transformer->expects($this->any())
                ->method('transform')
                ->will(
                    $this->returnCallback(
                        function ($value) use ($prefix) {
                            return "$prefix-$value";
                        }
                    )
                );
        }

        return $transformer;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
     */
    public function getProductValueMock($code)
    {
        $productValue = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
            ->setMethods(array('setData', '__toString'))
            ->getMock();

        $productValue->code = $code;

        $productValue->expects($this->any())
            ->method('setData')
            ->will(
                $this->returnCallback(
                    function ($data) use ($productValue) {
                        $productValue->data = $data;
                    }
                )
            );

        return $productValue;
    }

    /**
     * @param ProductValueInterface $productValue
     */
    public function addProductValue($productValue)
    {
        $this->productValues[$productValue->code] = $productValue;
    }

    /**
     * @param string $columnCode
     *
     * @return array
     */
    public function getValueErrors($columnCode)
    {
        return isset($this->valueErrors[$columnCode])
            ? $this->valueErrors[$columnCode]
            : array();
    }

    /**
     * @return array
     */
    public function getPropertyErrors()
    {
        return $this->propertyErrors;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $columnCode
     * @param string $backendType
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     */
    protected function addAttributeColumn(
        $columnCode,
        $backendType,
        $attributeCode = null,
        $localeCode = null,
        $scopeCode = null
    ) {
        if (!$attributeCode) {
            $attributeCode = $columnCode;
        }
        if (!isset($this->attributes[$attributeCode])) {
            $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
            $attribute->expects($this->any())
                ->method('getCode')
                ->will($this->returnValue($attributeCode));
            $attribute->expects($this->any())
                ->method('getBackendType')
                ->will($this->returnValue($backendType));
            $this->attributes[$attributeCode] = $attribute;
        }
        $this->columns[$columnCode] = array(
            'attribute' => $this->attributes[$attributeCode],
            'code' => $attributeCode,
            'locale' => $localeCode,
            'scope'  => $scopeCode
        );
    }
}
