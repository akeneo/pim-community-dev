<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatProductNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatProductNormalizerTest extends \PHPUnit_Framework_TestCase
{
    protected $normalizer;

    protected $mediaManager;

    /**
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Model\ProductInterface', 'csv', true],
            ['Pim\Bundle\CatalogBundle\Model\ProductInterface', 'xml', false],
            ['Pim\Bundle\CatalogBundle\Model\ProductInterface', 'json', false],
            ['Pim\Bundle\CatalogBundle\Model\Product', 'csv', true],
            ['Pim\Bundle\CatalogBundle\Model\Product', 'xml', false],
            ['Pim\Bundle\CatalogBundle\Model\Product', 'json', false],
            ['stdClass', 'csv', false],
            ['stdClass', 'xml', false],
            ['stdClass', 'json', false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->mediaManager = $this->getMediaManagerMock();
        $this->normalizer = new FlatProductNormalizer($this->mediaManager);
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
        $now    = new \DateTime();
        $media  = $this->getMediaMock();
        $this->mediaManager
            ->expects($this->any())
            ->method('getExportPath')
            ->with($media)
            ->will($this->returnValue('files/media.jpg'));

        $values = new ArrayCollection(
            [
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
                $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
                $this->getValueMock(
                    $this->getAttributeMock('elements'),
                    new ArrayCollection(['roue', 'poignées', 'benne'])
                ),
                $this->getValueMock($this->getAttributeMock('visual'), $media),
                $this->getValueMock($this->getAttributeMock('weight'), $this->getMetricMock('73', 'KILOGRAM')),
            ]
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = [
            'sku'         => 'KB0001',
            'family'      => 'garden-tool',
            'groups'      => null,
            'categories'  => 'cat1, cat2, cat3',
            'elements'    => 'roue,poignées,benne',
            'exportedAt'  => $now->format('m/d/Y'),
            'name-en_US'  => 'Wheelbarrow',
            'name-es_ES'  => 'Carretilla',
            'name-fr_FR'  => 'Brouette',
            'visual'      => 'files/media.jpg',
            'weight'      => '73.0000',
            'weight-unit' => 'KILOGRAM',
            'enabled'     => (int) true
        ];

        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv'));
    }

    /**
     * Test related method
     */
    public function testNormalizeProductWithScope()
    {
        $now    = new \DateTime();

        $values = new ArrayCollection(
            [
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brou.', 'fr_FR', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brouette', 'fr_FR', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'WB', 'en_US', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Wheelbarrow', 'en_US', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carret.', 'es_ES', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carretilla', 'es_ES', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
                $this->getValueMock(
                    $this->getAttributeMock('elements'),
                    new ArrayCollection(['roue', 'poignées', 'benne'])
                ),
            ]
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = [
            'sku'           => 'KB0001',
            'family'        => 'garden-tool',
            'groups'        => null,
            'categories'    => 'cat1, cat2, cat3',
            'elements'      => 'roue,poignées,benne',
            'exportedAt'    => $now->format('m/d/Y'),
            'name-en_US-ecommerce' => 'Wheelbarrow',
            'name-en_US-mobile' => 'WB',
            'name-es_ES-ecommerce' => 'Carretilla',
            'name-es_ES-mobile' => 'Carret.',
            'name-fr_FR-ecommerce' => 'Brouette',
            'name-fr_FR-mobile' => 'Brou.',
            'enabled'       => 1
        ];

        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv'));
    }

    /**
     * Test related method
     */
    public function testNormalizeOnScopeProductWithScope()
    {
        $now    = new \DateTime();

        $values = new ArrayCollection(
            [
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brou.', 'fr_FR', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Brouette', 'fr_FR', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'WB', 'en_US', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Wheelbarrow', 'en_US', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carret.', 'es_ES', 'mobile'),
                $this->getValueMock($this->getAttributeMock('name', true, true), 'Carretilla', 'es_ES', 'ecommerce'),
                $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
                $this->getValueMock(
                    $this->getAttributeMock('elements'),
                    new ArrayCollection(['roue', 'poignées', 'benne'])
                ),
            ]
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = [
            'sku'           => 'KB0001',
            'family'        => 'garden-tool',
            'groups'        => null,
            'categories'    => 'cat1, cat2, cat3',
            'elements'      => 'roue,poignées,benne',
            'exportedAt'    => $now->format('m/d/Y'),
            'name-en_US-ecommerce' => 'Wheelbarrow',
            'name-es_ES-ecommerce' => 'Carretilla',
            'name-fr_FR-ecommerce' => 'Brouette',
            'enabled'       => 1
        ];

        $context = ['scopeCode' => 'ecommerce'];

        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv', $context));
    }

    /**
     * Test related method
     */
    public function testNormalizeProductWithoutFamily()
    {
        $now = new \DateTime();
        $values = new ArrayCollection(
            [
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
                $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
                $this->getValueMock(
                    $this->getAttributeMock('elements'),
                    new ArrayCollection(['roue', 'poignées', 'benne'])
                ),
            ]
        );
        $identifier = $this->getValueMock(
            $this->getAttributeMock('sku', false, false, 'pim_catalog_identifier'),
            'KB0001'
        );
        $product    = $this->getProductMock($identifier, $values, null, 'cat1, cat2, cat3');

        $result = [
            'sku'        => 'KB0001',
            'family'     => '',
            'groups'     => null,
            'categories' => 'cat1, cat2, cat3',
            'elements'   => 'roue,poignées,benne',
            'exportedAt' => $now->format('m/d/Y'),
            'name-en_US' => 'Wheelbarrow',
            'name-es_ES' => 'Carretilla',
            'name-fr_FR' => 'Brouette',
            'enabled'    => 1
        ];

        $this->assertArrayEquals($result, $this->normalizer->normalize($product, 'csv'));
    }

    /**
     * @expectedException Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException
     */
    public function testNormalizeProductWithoutIdentifier()
    {
        $now = new \DateTime();
        $values = new ArrayCollection(
            [
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
                $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
                $this->getValueMock(
                    $this->getAttributeMock('elements'),
                    new ArrayCollection(['roue', 'poignées', 'benne'])
                ),
            ]
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
     * @param Attribute $identifier
     * @param array     $values
     * @param Family    $family
     * @param string    $categories
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
            ->will($this->returnValue([]));

        $product->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        return $product;
    }

    /**
     * @param Attribute $attribute
     * @param mixed     $data
     * @param string    $locale
     * @param string    $scope
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
     * @param boolean $translatable
     * @param boolean $scopable
     * @param string  $type
     *
     * @return Attribute
     */
    protected function getAttributeMock($code, $translatable = false, $scopable = false, $type = 'pim_catalog_text')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('isTranslatable')
            ->will($this->returnValue($translatable));

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
     * @return \Pim\Bundle\CatalogBundle\Model\Media
     */
    protected function getMediaMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');
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

    protected function getMetricMock($data, $unit)
    {
        $metric = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');

        $metric->expects($this->any())->method('getData')->will($this->returnValue($data));
        $metric->expects($this->any())->method('getUnit')->will($this->returnValue($unit));

        return $metric;
    }
}
