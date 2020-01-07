<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Normalizer\Flat;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIntegration extends TestCase
{
    public function testProduct()
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');

        $this->assertEquals(
            $product->getRawValues()['a_date']['<all_channels>']['<all_locales>'],
            '2016-06-13T00:00:00+02:00'
        );

        $flatProduct = $this->get('pim_versioning.serializer')->normalize($product, 'flat');
        $mediaAttributes = ['a_file', 'an_image', 'a_localizable_image-en_US', 'a_localizable_image-fr_FR'];
        $flatProduct = $this->sanitizeMediaAttributeData($flatProduct, $mediaAttributes);

        $expected = [
            'family' => 'familyA',
            'groups' => 'groupA,groupB',
            'categories' => 'categoryA1,categoryB',
            'parent' => '',
            'X_SELL-groups' => 'groupB',
            'X_SELL-products' => 'bar',
            'X_SELL-product_models' => '',
            'UPSELL-groups' => 'groupA',
            'UPSELL-products' => '',
            'UPSELL-product_models' => '',
            'SUBSTITUTION-groups' => '',
            'SUBSTITUTION-products' => '',
            'SUBSTITUTION-product_models' => '',
            'PACK-groups' => '',
            'PACK-products' => 'bar,baz',
            'PACK-product_models' => '',
            'a_date' => '2016-06-13T00:00:00+02:00',
            'a_file' => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
            'a_localizable_image-en_US' => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
            'a_localizable_image-fr_FR' => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
            'a_localized_and_scopable_text_area-en_US-ecommerce' => 'a text area for ecommerce in English',
            'a_localized_and_scopable_text_area-en_US-tablet' => 'a text area for tablets in English',
            'a_localized_and_scopable_text_area-fr_FR-tablet' => 'une zone de texte pour les tablettes en français',
            'a_metric' => '987654321987.1234',
            'a_metric-unit' => 'KILOWATT',
            'a_metric_negative' => '-20.5000',
            'a_metric_negative-unit' => 'CELSIUS',
            'a_metric_without_decimal' => '98',
            'a_metric_without_decimal-unit' => 'CENTIMETER',
            'a_metric_without_decimal_negative' => '-20',
            'a_metric_without_decimal_negative-unit' => 'CELSIUS',
            'a_multi_select' => 'optionA,optionB',
            'a_number_float' => '12.5678',
            'a_number_float_negative' => '-99.8732',
            'a_number_integer' => '42',
            'a_number_integer_negative' => '-42',
            'a_price-EUR' => '56.53',
            'a_price-USD' => '45.00',
            'a_price_without_decimal-EUR' => '56.00',
            'a_price_without_decimal-USD' => '-45.00',
            'a_ref_data_multi_select' => 'fabricA,fabricB',
            'a_ref_data_simple_select' => 'colorB',
            'a_scopable_price-ecommerce-USD' => '20.00',
            'a_scopable_price-tablet-EUR' => '17.00',
            'a_simple_select' => 'optionB',
            'a_text' => 'this is a text',
            'a_text_area' => 'this is a very very very very very long  text',
            'a_yes_no' => '1',
            'an_image' => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
            'sku' => 'foo',
            '123' => 'a text for an attribute with numerical code',
            'enabled' => 1
        ];

        $expected = $this->sanitizeMediaAttributeData($expected, $mediaAttributes);

        $this->assertEquals($expected, $flatProduct);

        $this->assertEquals(
            $product->getRawValues()['a_date']['<all_channels>']['<all_locales>'],
            $flatProduct['a_date']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    /**
     * @param array $data
     * @param array $mediaAttributes
     *
     * @return array
     */
    private function sanitizeMediaAttributeData(array $data, array $mediaAttributes)
    {
        foreach ($data as $attribute => $value) {
            if (in_array($attribute, $mediaAttributes)) {
                $data[$attribute] = MediaSanitizer::sanitize($value);
            }
        }

        return $data;
    }
}
