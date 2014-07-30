<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected $normalizer;

    protected $valuesFilters;

    protected $flatProductValueNormalizerFilter;

    /**
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'json', false),
            array('Pim\Bundle\CatalogBundle\Model\Product', 'csv', true),
            array('Pim\Bundle\CatalogBundle\Model\Product', 'xml', false),
            array('Pim\Bundle\CatalogBundle\Model\Product', 'json', false),
            array('stdClass', 'csv', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'json', false),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->mediaManager = $this->getMediaManagerMock();
        $this->flatProductValueNormalizerFilter = $this->getFlatProductValueNormalizerFilterMock();

        $this->normalizer = new ProductNormalizer($this->mediaManager);
        $this->serializer = $this->getMockForAbstractClass(
            '\Pim\Bundle\TransformBundle\Tests\Unit\Normalizer\Flat\AbstractSerializer'
        );
        $this->normalizer->setSerializer($this->serializer);
    }

    /**
     * @param string  $class
     * @param string  $format
     * @param boolean $isSupported
     *
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMock($class);

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
    }

    /**
     * Test related method
     */
    public function testNormalizeProduct()
    {
        $this->mediaManager
            ->expects($this->any())
            ->method('getExportPath')
            ->will($this->returnValue('files/media.jpg'));

        $values = new ArrayCollection(
            array(
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            )
        );

        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );

        $context = array(
            'identifier'  => $identifier,
            'scopeCode'   => null,
            'localeCodes' => array()
        );

        $this->flatProductValueNormalizerFilter
            ->expects($this->any())
            ->method('filter')
            ->with($values, $context)
            ->will($this->returnValue($values));

        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = array(
            'sku'         => 'KB0001',
            'family'      => 'garden-tool',
            'groups'      => null,
            'categories'  => 'cat1, cat2, cat3',
            'name-en_US'  => 'Wheelbarrow',
            'name-es_ES'  => 'Carretilla',
            'name-fr_FR'  => 'Brouette',
            'enabled'     => (int) true
        );

        $this->normalizer->setFilters(array($this->flatProductValueNormalizerFilter));
        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv', $context));
    }

    /**
     * Test related method
     */
    public function testNormalizeProductWithScope()
    {

        $values = new ArrayCollection(
            array(
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brou.', 'fr_FR', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brouette', 'fr_FR', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'WB', 'en_US', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Wheelbarrow', 'en_US', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carret.', 'es_ES', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carretilla', 'es_ES', 'ecommerce'),
            )
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );

        $context = array(
            'identifier'  => $identifier,
            'scopeCode'   => null,
            'localeCodes' => array()
        );

        $this->flatProductValueNormalizerFilter
            ->expects($this->any())
            ->method('filter')
            ->with($values, $context)
            ->will($this->returnValue($values));

        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = array(
            'sku'           => 'KB0001',
            'family'        => 'garden-tool',
            'groups'        => null,
            'categories'    => 'cat1, cat2, cat3',
            'name-en_US-ecommerce' => 'Wheelbarrow',
            'name-en_US-mobile' => 'WB',
            'name-es_ES-ecommerce' => 'Carretilla',
            'name-es_ES-mobile' => 'Carret.',
            'name-fr_FR-ecommerce' => 'Brouette',
            'name-fr_FR-mobile' => 'Brou.',
            'enabled'       => 1
        );

        $this->normalizer->setFilters(array($this->flatProductValueNormalizerFilter));
        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv', $context));
    }

    /**
     * Test related method
     */
    public function testNormalizeOnScopeProductWithScope()
    {

        $rawValues = array(
            $this->getValueMock($this->getAttributeMock('name', true, true), 'Brou.', 'fr_FR', 'mobile'),
            $this->getValueMock($this->getAttributeMock('name', true, true), 'Brouette', 'fr_FR', 'ecommerce'),
            $this->getValueMock($this->getAttributeMock('name', true, true), 'WB', 'en_US', 'mobile'),
            $this->getValueMock($this->getAttributeMock('name', true, true), 'Wheelbarrow', 'en_US', 'ecommerce'),
            $this->getValueMock($this->getAttributeMock('name', true, true), 'Carret.', 'es_ES', 'mobile'),
            $this->getValueMock($this->getAttributeMock('name', true, true), 'Carretilla', 'es_ES', 'ecommerce'),
        );

        $values         = new ArrayCollection($rawValues);
        $filteredValues = new ArrayCollection(
            array(
                $rawValues[1],
                $rawValues[3],
                $rawValues[5],
            )
        );

        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );

        $family  = $this->getFamilyMock('garden-tool');
        $product        = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $context = array(
            'identifier'  => $identifier,
            'scopeCode'   => 'ecommerce',
            'localeCodes' => array()
        );

        $this->flatProductValueNormalizerFilter
            ->expects($this->any())
            ->method('filter')
            ->with($values, $context)
            ->will($this->returnValue($filteredValues));

        $result = array(
            'sku'           => 'KB0001',
            'family'        => 'garden-tool',
            'groups'        => null,
            'categories'    => 'cat1, cat2, cat3',
            'name-en_US-ecommerce' => 'Wheelbarrow',
            'name-es_ES-ecommerce' => 'Carretilla',
            'name-fr_FR-ecommerce' => 'Brouette',
            'enabled'       => 1
        );

        $context = array('scopeCode' => 'ecommerce');

        $this->normalizer->setFilters(array($this->flatProductValueNormalizerFilter));
        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv', $context));
    }

    /**
     * Test related method
     */
    public function testNormalizeProductWithoutFamily()
    {
        $values = new ArrayCollection(
            array(
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            )
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );

        $context = array(
            'identifier'  => $identifier,
            'scopeCode'   => null,
            'localeCodes' => array()
        );

        $this->flatProductValueNormalizerFilter
            ->expects($this->any())
            ->method('filter')
            ->with($values, $context)
            ->will($this->returnValue($values));

        $product    = $this->getProductMock($identifier, $values, null, 'cat1, cat2, cat3');

        $result = array(
            'sku'        => 'KB0001',
            'family'     => '',
            'groups'     => null,
            'categories' => 'cat1, cat2, cat3',
            'name-en_US' => 'Wheelbarrow',
            'name-es_ES' => 'Carretilla',
            'name-fr_FR' => 'Brouette',
            'enabled'    => 1
        );

        $this->normalizer->setFilters(array($this->flatProductValueNormalizerFilter));
        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv', $context));
    }

    /**
     * @expectedException Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException
     */
    public function testNormalizeProductWithoutIdentifier()
    {
        $values = new ArrayCollection(
            array(
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            )
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock(null, $values, $family, 'cat1,cat2,cat3');

        $this->normalizer->normalize($product, 'csv');
    }

    /**
     * Assert that element orders and values of two arrays are equal
     *
     * @param array $a
     * @param array $b
     */
    protected function assertArrayEquals(array $a, array $b)
    {
        $this->assertSame(array_keys($a), array_keys($b));
        $this->assertSame($a, $b);
    }

    /**
     * @param AbstractAttribute $identifier
     * @param array             $values
     * @param Family            $family
     * @param string            $categories
     *
     * @return ProductInterface
     */
    protected function getProductMock(
        $identifier = null,
        ArrayCollection $values = null,
        $family = null,
        $categories = ''
    ) {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        if ($identifier) {
            $identifierReturn = $this->returnValue($identifier);
        } else {
            $identifierReturn = $this->throwException(new MissingIdentifierException($product));
        }

        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($identifierReturn);

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $product->expects($this->any())
            ->method('getCategoryCodes')
            ->will($this->returnValue($categories));

        $product->expects($this->any())
            ->method('getAssociations')
            ->will($this->returnValue(array()));

        $product->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        return $product;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param mixed             $data
     * @param string            $locale
     * @param string            $scope
     *
     * @return ProductValue
     */
    protected function getValueMock($attribute = null, $data = null, $locale = null, $scope = null)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        $value->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue($scope));

        return $value;
    }

    /**
     * @param string  $code
     * @param boolean $localizable
     * @param boolean $scopable
     * @param string  $type
     *
     * @return AbstractAttribute
     */
    protected function getAttributeMock($code, $localizable = false, $scopable = false, $type = 'pim_catalog_text')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('isLocalizable')
            ->will($this->returnValue($localizable));

        $attribute->expects($this->any())
            ->method('isScopable')
            ->will($this->returnValue($scopable));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        return $attribute;
    }

    /**
     * @param string $code
     *
     * @return Family
     */
    protected function getFamilyMock($code)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $family;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductMedia
     */
    protected function getMediaMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductMedia');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    protected function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\TransformBundle\Normalizer\Filter\FlatProductValueNormalizerFilter
     */
    protected function getFlatProductValueNormalizerFilterMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\TransformBundle\Filter\FlatProductValueFilter')
            ->disableOriginalConstructor()
            ->setMethods(array('filter'))
            ->getMock();
    }

    protected function getMetricMock($data, $unit)
    {
        $metric = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');

        $metric->expects($this->any())->method('getData')->will($this->returnValue($data));
        $metric->expects($this->any())->method('getUnit')->will($this->returnValue($unit));

        return $metric;
    }
}
