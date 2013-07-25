<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatProductNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Exception\MissingIdentifierException;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatProductNormalizerTest extends \PHPUnit_Framework_TestCase
{
    private $normalizer;

    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\ProductBundle\Model\ProductInterface', 'csv',  true),
            array('Pim\Bundle\ProductBundle\Model\ProductInterface', 'json', false),
            array('stdClass',                                        'csv',  false),
            array('stdClass',                                        'json', false),
        );
    }

    protected function setUp()
    {
        $this->normalizer = new FlatProductNormalizer();
    }

    /**
     * @dataProvider getSupportNormalizationData
     */
    public function testSupportNormalization($class, $format, $isSupported)
    {
        $data = $this->getMock($class);

        $this->assertSame($isSupported, $this->normalizer->supportsNormalization($data, $format));
    }

    public function testNormalizeProduct()
    {
        $now = new \DateTime;
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock($this->getAttributeMock('elements'), new ArrayCollection(array(
                'roue', 'poignées', 'benne'
            ))),
        );
        $identifier = $this->getValueMock($this->getAttributeMock('sku', false, 'pim_product_identifier'), 'KB0001');
        $family     = $this->getFamilyMock('garden-tool');
        $product    = $this->getProductMock($identifier, $values, $family, 'cat1, cat2, cat3');

        $result = array(
            'sku'        => 'KB0001',
            'family'     => 'garden-tool',
            'name_fr_FR' => 'Brouette',
            'name_en_US' => 'Wheelbarrow',
            'name_es_ES' => 'Carretilla',
            'exportedAt' => $now->format('r'),
            'elements'   => '"roue,poignées,benne"',
            'categories' => '"cat1, cat2, cat3"',
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'csv')
        );
    }

    public function testNormalizeProductWithoutFamily()
    {
        $now = new \DateTime;
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock($this->getAttributeMock('elements'), new ArrayCollection(array(
                'roue', 'poignées', 'benne'
            ))),
        );
        $identifier = $this->getValueMock($this->getAttributeMock('sku', false, 'pim_product_identifier'), 'KB0001');
        $product    = $this->getProductMock($identifier, $values, null, 'cat1, cat2, cat3');

        $result = array(
            'sku'        => 'KB0001',
            'family'     => '',
            'name_fr_FR' => 'Brouette',
            'name_en_US' => 'Wheelbarrow',
            'name_es_ES' => 'Carretilla',
            'exportedAt' => $now->format('r'),
            'elements'   => '"roue,poignées,benne"',
            'categories' => '"cat1, cat2, cat3"',
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'csv')
        );
    }

    /**
     * @expectedException Pim\Bundle\ProductBundle\Exception\MissingIdentifierException
     */
    public function testNormalizeProductWithoutIdentifier()
    {
        $now = new \DateTime;
        $values = array(
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock($this->getAttributeMock('elements'), new ArrayCollection(array(
                'roue', 'poignées', 'benne'
            ))),
        );
        $family  = $this->getFamilyMock('garden-tool');
        $product = $this->getProductMock(null, $values, $family, 'cat1, cat2, cat3');

        $this->normalizer->normalize($product, 'csv');
    }

    private function getProductMock($identifier = null, array $values = array(), $family = null, $categories = '')
    {
        $product = $this->getMock('Pim\Bundle\ProductBundle\Entity\Product');
        if ($identifier) {
            $identifierReturn = $this->returnValue($identifier) ;
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
            ->method('getCategoryTitlesAsString')
            ->will($this->returnValue($categories));

        return $product;
    }

    private function getValueMock($attribute = null, $data = null, $locale = null)
    {
        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue');

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

    private function getAttributeMock($code, $translatable = false, $type = 'pim_product_text')
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

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

    private function getFamilyMock($code)
    {
        $family = $this->getMock('Pim\Bundle\ProductBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $family;
    }
}
