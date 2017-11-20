<?php

namespace PimEnterprise\Bundle\ConnectorBundle\tests\integration\Export\ProductModel;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                  Attribute groups                   |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------+-----------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * | Redactor |      View     |     -         | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 */
class ExportProductModelsWithPermissionsIntegration extends AbstractProductModelExportTestCase
{
    public function testProductModelViewableByRedactor()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;an_image;a_date;a_file;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localizable_image-zh_CN;a_localized_and_scopable_text_area-en_US-ecommerce;a_localized_and_scopable_text_area-en_US-ecommerce_china;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_localized_and_scopable_text_area-zh_CN-ecommerce_china;a_metric;a_metric-unit;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-ecommerce-CNY;a_scopable_price-ecommerce-EUR;a_scopable_price-ecommerce-USD;a_scopable_price-ecommerce_china-CNY;a_scopable_price-ecommerce_china-EUR;a_scopable_price-ecommerce_china-USD;a_scopable_price-tablet-CNY;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD
root_product_model_visible_for_redactor;familyVariantA1;;categoryA2;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_model_export', 'mary');
        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductModelViewableByManager()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;an_image;a_date;a_file;a_localizable_image-de_DE;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localizable_image-zh_CN;a_localized_and_scopable_text_area-de_DE-tablet;a_localized_and_scopable_text_area-en_US-ecommerce;a_localized_and_scopable_text_area-en_US-ecommerce_china;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_localized_and_scopable_text_area-zh_CN-ecommerce_china;a_metric;a_metric-unit;a_multi_select;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-ecommerce-CNY;a_scopable_price-ecommerce-EUR;a_scopable_price-ecommerce-USD;a_scopable_price-ecommerce_china-CNY;a_scopable_price-ecommerce_china-EUR;a_scopable_price-ecommerce_china-USD;a_scopable_price-tablet-CNY;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD
root_product_model_visible_for_redactor;familyVariantA1;;categoryA2;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
root_product_model_visible_for_manager_only;familyVariantA1;;categoryB;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_model_export', 'julia');
        $this->assertSame($expectedCsv, $csv);
    }

    public function testProductModelExportWithNotGrantedPermissionsOnCategory()
    {
        $expectedCsv = <<<CSV
code;family_variant;parent;categories;an_image;a_date;a_file;a_localizable_image-en_US;a_localizable_image-fr_FR;a_localizable_image-zh_CN;a_localized_and_scopable_text_area-en_US-ecommerce;a_localized_and_scopable_text_area-en_US-ecommerce_china;a_localized_and_scopable_text_area-en_US-tablet;a_localized_and_scopable_text_area-fr_FR-tablet;a_localized_and_scopable_text_area-zh_CN-ecommerce_china;a_metric;a_metric-unit;a_number_float;a_number_float_negative;a_number_integer;a_price-CNY;a_price-EUR;a_price-USD;a_ref_data_multi_select;a_ref_data_simple_select;a_scopable_price-ecommerce-CNY;a_scopable_price-ecommerce-EUR;a_scopable_price-ecommerce-USD;a_scopable_price-ecommerce_china-CNY;a_scopable_price-ecommerce_china-EUR;a_scopable_price-ecommerce_china-USD;a_scopable_price-tablet-CNY;a_scopable_price-tablet-EUR;a_scopable_price-tablet-USD
root_product_model_visible_for_redactor;familyVariantA1;;categoryA2;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

CSV;

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_product_model_export', 'mary');
        $this->assertSame($expectedCsv, $csv);
    }
}
