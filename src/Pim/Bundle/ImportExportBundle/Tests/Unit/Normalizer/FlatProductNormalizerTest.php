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
    private $normalizer;

    /**
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'csv',  true),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'json', false),
            array('stdClass',                                        'csv',  false),
            array('stdClass',                                        'json', false),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FlatProductNormalizer();
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
        $now = new \DateTime();
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock(
                $this->getAttributeMock('elements'),
                new ArrayCollection(array('roue', 'poignées', 'benne'))
            ),
        );
        $identifier = $this->getValueMock($this->getAttributeMock('sku', false, 'pim_catalog_identifier'), 'KB0001');
        $family     = $this->getFamilyMock('garden-tool');
        $product    = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = array(
            'sku'        => 'KB0001',
            'family'     => 'garden-tool',
            'name-fr_FR' => 'Brouette',
            'name-en_US' => 'Wheelbarrow',
            'name-es_ES' => 'Carretilla',
            'exportedAt' => $now->format('m/d/Y'),
            'elements'   => 'roue,poignées,benne',
            'categories' => 'cat1, cat2, cat3',
            'variant_group' => '',
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'csv')
        );
    }

    /**
     * Test related method
     */
    public function testNormalizeProductWithoutFamily()
    {
        $now = new \DateTime();
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock(
                $this->getAttributeMock('elements'),
                new ArrayCollection(array('roue', 'poignées', 'benne'))
            ),
        );
        $identifier = $this->getValueMock($this->getAttributeMock('sku', false, 'pim_catalog_identifier'), 'KB0001');
        $product    = $this->getProductMock($identifier, $values, null, 'cat1, cat2, cat3');

        $result = array(
            'sku'        => 'KB0001',
            'family'     => '',
            'name-fr_FR' => 'Brouette',
            'name-en_US' => 'Wheelbarrow',
            'name-es_ES' => 'Carretilla',
            'exportedAt' => $now->format('m/d/Y'),
            'elements'   => 'roue,poignées,benne',
            'categories' => 'cat1, cat2, cat3',
            'variant_group' => '',
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'csv')
        );
    }

    /**
     * @expectedException Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException
     */
    public function testNormalizeProductWithoutIdentifier()
    {
        $now = new \DateTime();
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock(
                $this->getAttributeMock('elements'),
                new ArrayCollection(array('roue', 'poignées', 'benne'))
            ),
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock(null, $values, $family, 'cat1,cat2,cat3');

        $this->normalizer->normalize($product, 'csv');
    }

    /**
     * @param ProductAttribute $identifier
     * @param array            $values
     * @param Family           $family
     * @param string           $categories
     *
     * @return ProductInterface
     */
    private function getProductMock($identifier = null, array $values = array(), $family = null, $categories = '')
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');
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

        return $product;
    }

    /**
     * @param ProductAttribute $attribute
     * @param mixed            $data
     * @param mixed            $locale
     *
     * @return ProductValue
     */
    private function getValueMock($attribute = null, $data = null, $locale = null)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        $value->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue($locale));

        return $value;
    }

    /**
     * @param string  $code
     * @param boolean $translatable
     * @param string  $type
     *
     * @return ProductAttribute
     */
    private function getAttributeMock($code, $translatable = false, $type = 'pim_catalog_text')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

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
    private function getFamilyMock($code)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $family;
    }
}
