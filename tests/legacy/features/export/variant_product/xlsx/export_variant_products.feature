@javascript
Feature: Export variant products through XLSX export
  In order to export shoes of the collection 2016 to my ecommerce channel
  As a product manager
  I need to be able to export variant products as a XLSX file

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Export variant products through XLSX
    Given I am on the "xlsx_summer_2016_shoes_products_export" export job page
    And I launch the export job
    And I wait for the "xlsx_summer_2016_shoes_products_export" job to finish
    Then exported xlsx file of "xlsx_summer_2016_shoes_products_export" should contain:
      |sku       |categories                                  |enabled|family|parent    |groups|brand|collection|color|composition|description-en_US-ecommerce|ean|erp_name-en_US|eu_shoes_size|image|keywords-en_US|material|meta_description-en_US|meta_title-en_US|name-en_US|notice|price-EUR|price-USD|size|sole_composition|supplier|top_composition|variation_image|variation_name-en_US|weight|weight-unit|
      |1111111173|master_men_shoes,supplier_abibas            |1      |shoes |brogueshoe|      ||summer_2016|||||Brogue shoe|410||||||brogueshoe||267.00||||abibas||||900.0000|GRAM|
      |1111111174|master_men_shoes,supplier_abibas            |1      |shoes |brogueshoe|      ||summer_2016|||||Brogue shoe|400||||||brogueshoe||267.00||||abibas||||900.0000|GRAM|
      |1111111175|master_men_shoes,supplier_abibas            |1      |shoes |brogueshoe|      ||summer_2016|||||Brogue shoe|390||||||brogueshoe||267.00||||abibas||||800.0000|GRAM|
      |1111111176|master_men_shoes,supplier_abibas            |1      |shoes |brogueshoe|      ||summer_2016|||||Brogue shoe|380||||||brogueshoe||267.00||||abibas||||800.0000|GRAM|
      |1111111199|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|410||||||derby||123.00||||abibas||||900.0000|GRAM|
      |1111111200|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|400||||||derby||123.00||||abibas||||900.0000|GRAM|
      |1111111201|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|390||||||derby||123.00||||abibas||||800.0000|GRAM|
      |1111111202|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|380||||||derby||123.00||||abibas||||800.0000|GRAM|
      |1111111203|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|370||||||derby||123.00||||abibas||||800.0000|GRAM|
      |1111111204|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |derby     |      ||summer_2016|||||Derby shoe|360||||||derby||123.00||||abibas||||800.0000|GRAM|
      |1111111229|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |galesh    |      ||summer_2016|||||Galesh shoe|410||||||galesh||144.00||||abibas||||900.0000|GRAM|
      |1111111230|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |galesh    |      ||summer_2016|||||Galesh shoe|400||||||galesh||144.00||||abibas||||900.0000|GRAM|
      |1111111231|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |galesh    |      ||summer_2016|||||Galesh shoe|390||||||galesh||144.00||||abibas||||800.0000|GRAM|
      |1111111232|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |galesh    |      ||summer_2016|||||Galesh shoe|380||||||galesh||144.00||||abibas||||800.0000|GRAM|
      |1111111233|master_men_shoes,print_shoes,supplier_abibas|1      |shoes |galesh    |      ||summer_2016|||||Galesh shoe|360||||||galesh||144.00||||abibas||||800.0000|GRAM|
