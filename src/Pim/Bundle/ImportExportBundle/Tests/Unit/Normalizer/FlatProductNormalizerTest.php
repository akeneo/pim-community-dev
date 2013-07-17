<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FlatProductNormalizer;
use Doctrine\Common\Collections\ArrayCollection;

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
            $this->getValueMock($this->getAttributeMock('sku'), 'KB0001'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
            $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
            $this->getValueMock($this->getAttributeMock('exportedAt'), $now),
            $this->getValueMock($this->getAttributeMock('elements'), new ArrayCollection(array(
                'roue', 'poignées', 'benne'
            ))),
        );
        $product = $this->getProductMock($values);

        $result = array(
            'sku'        => 'KB0001',
            'name_fr_FR' => 'Brouette',
            'name_en_US' => 'Wheelbarrow',
            'name_es_ES' => 'Carretilla',
            'exportedAt' => $now->format('r'),
            'elements'   => 'roue,poignées,benne',
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'csv')
        );
    }

    private function getProductMock(array $values = array())
    {
        $product = $this->getMock('Pim\Bundle\ProductBundle\Entity\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

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

    private function getAttributeMock($code, $translatable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }
}
