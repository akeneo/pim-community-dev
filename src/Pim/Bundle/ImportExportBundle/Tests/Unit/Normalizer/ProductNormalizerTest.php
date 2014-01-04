<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ImportExportBundle\Normalizer\ProductNormalizer;

/**
 * Product normalizer test
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizerTest extends \PHPUnit_Framework_TestCase
{
    private $normalizer;

    /**
     * Prepare the normalizer
     */
    protected function setUp()
    {
        $this->normalizer = new ProductNormalizer($this->getRouterMock());
        $this->normalizer->setChannel($this->getChannelMock());
    }

    /**
     * Provider for restSupportNormalization
     *
     * @return array
     */
    public static function getSupportNormalizationData()
    {
        return array(
            array('Pim\Bundle\CatalogBundle\Model\Product', 'json', true),
            array('Pim\Bundle\CatalogBundle\Model\Product', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Model\Product', 'csv',  false),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'json', true),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'xml', true),
            array('Pim\Bundle\CatalogBundle\Model\ProductInterface', 'csv', false),
            array('stdClass', 'json', false),
            array('stdClass', 'xml', false),
            array('stdClass', 'csv', false),
        );
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
     * Test the normalize method
     */
    public function testNormalizeProduct()
    {
        $values = new ArrayCollection(
            array(
                $this->getValueMock($this->getAttributeMock('sku'), 'KB0001'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Brouette', 'fr_FR'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Wheelbarrow', 'en_US'),
                $this->getValueMock($this->getAttributeMock('name', true), 'Carretilla', 'es_ES'),
                $this->getValueMock(
                    $this->getAttributeMock('tags'),
                    new ArrayCollection(array('home', 'garden', 'tools'))
                ),
            )
        );
        $product = $this->getProductMock($values);

        $result = array(
            'KB0001' => array(
                'sku' => array(
                    'en_US' => 'KB0001',
                    'fr_FR' => 'KB0001'
                ),
                'name' => array(
                    'en_US' => 'Wheelbarrow',
                    'fr_FR' => 'Brouette'
                ),
                'tags' => array(
                    'en_US' => 'home, garden, tools',
                    'fr_FR' => 'home, garden, tools'
                ),
                'resource' => 'http://akeneo-pim.local/api/rest/ecommerce/products/KB0001.json'
            ),
        );

        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'json')
        );
    }

    /**
     * Get a router mock
     * @return Router
     */
    protected function getRouterMock()
    {
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock();

        $router->expects($this->any())
                ->method('generate')
                ->with(
                    $this->logicalOr(
                        $this->stringContains('oro_api_get_product'),
                        $this->equalTo(
                            array(
                                'scope' => 'ecommerce',
                                'identifier' => 'KB0001'
                            )
                        )
                    )
                )
                ->will($this->returnValue('http://akeneo-pim.local/api/rest/ecommerce/products/KB0001.json'));

        return $router;
    }

    /**
     * Get a router mock
     * @return Channel
     */
    protected function getChannelMock()
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $locales = new ArrayCollection(
            array(
                $this->getLocaleMock('en_US'),
                $this->getLocaleMock('fr_FR')
            )
        );

        $channel->expects($this->any())
                ->method('getLocales')
                ->will($this->returnValue($locales));

        return $channel;
    }

    /**
     * Get a mock of Locale entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    private function getLocaleMock($code)
    {
        $locale = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Locale', array('getCode'));

        $locale->expects($this->any())
                 ->method('getCode')
                 ->will($this->returnValue($code));

        return $locale;
    }

    /**
     * Get a product mock
     * @param ArrayCollection $values
     *
     * @return Product
     */
    private function getProductMock(ArrayCollection $values = null)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        $product->expects($this->any())
            ->method('getIdentifier')
            ->will(
                $this->returnValue(
                    $values->filter(
                        function ($value) {
                            return strtolower($value->getAttribute()->getCode()) == 'sku';
                        }
                    )->first()
                )
            );

        return $product;
    }

    /**
     * Get a product value mock
     * @param Attribute $attribute
     * @param mixed     $data
     * @param string    $locale
     *
     * @return ProductValue
     */
    private function getValueMock($attribute = null, $data = null, $locale = null)
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

        return $value;
    }

    /**
     * Get a product attribute mock
     * @param string  $code
     * @param boolean $translatable
     *
     * @return ProductValue
     */
    private function getAttributeMock($code, $translatable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('isTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }
}
