<?php

namespace Pim\Bundle\JSONConnectorBundle\Test\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\JSONConnectorBundle\Normalizer\ProductNormalizer;

/**
 * Product normalizer test
 * 
 * @copyright 2014 Sylvain Rascar <srascar@webnet.fr>
 * @author Sylvain Rascar <srascar@webnet.fr>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizerTest extends \PHPUnit_Framework_TestCase
{

    private $normalizer;

    /**
     * Prepare the normalizer
     */
    protected function setUp()
    {
        $this->normalizer = new ProductNormalizer();
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
                $this->getValueMock($this->getAttributeMock('available'), true),
                $this->getValueMock(
                    $this->getAttributeMock('tags'),
                    new ArrayCollection(array('home', 'garden', 'tools'))
                ),
            )
        );
        $family = $this->getFamilyMock('my_family');
        $categories = new ArrayCollection(
                array(
                    $this->getCategoryMock('my_category_1'),
                    $this->getCategoryMock('my_category_2'),
                    $this->getCategoryMock('my_category_3'),
                )
            );
        $product = $this->getProductMock($values, $family, $categories);

        $result = array(
                'sku' => "KB0001",
                'name' =>array(
                  'en_US' => "Wheelbarrow",
                ),
                'available' => true,
                'tags' => "home, garden, tools",
                'family' => "my_family",
                'categories' => "my_category_1, my_category_2, my_category_3"
              );


        $this->assertEquals(
            $result,
            $this->normalizer->normalize($product, 'json', array(
                'locales' => array('en_US'),
                'other_attributes' => array('family', 'categories'),
                ))
        );
    }
    
    
    
    /**
     * Get a channel mock
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
    private function getProductMock(ArrayCollection $values = null, $family = null, $categories = null)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($family));
        
        $product->expects($this->any())
            ->method('getCategories')
            ->will($this->returnValue($categories));
        
        return $product;
    }

    /**
     * Get a product value mock
     * @param ProductAttribute $attribute
     * @param mixed            $data
     * @param string           $locale
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
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('isTranslatable')
            ->will($this->returnValue($translatable));

        return $attribute;
    }

    /**
     * Get a family mock
     * @param string  $code
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

    /**
     * Get a category mock
     * @param string  $code
     *
     * @return Category
     */
    private function getCategoryMock($code)
    {
        $category = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Category');

        $category->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $category;
    }
}
