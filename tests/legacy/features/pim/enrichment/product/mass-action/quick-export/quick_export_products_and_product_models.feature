@javascript
Feature: Export products and product models
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products and product models to several channels

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully export 2 files on quick export
    When I display in the products grid the columns ID, Label, Model description
    And I search "amor"
    And I select row amor
    And I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And I should see "products_export_grid_context_en_US_ecommerce.csv" on the "Download generated files" dropdown button
    And I should see "product_models_export_grid_context_en_US_ecommerce.csv" on the "Download generated files" dropdown button

  Scenario: Successfully export the grid columns for quick export product models
    When I display in the products grid the columns ID, Label, Model description
    And I search "amor"
    And I select row amor
    And I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And second exported file of "csv_product_grid_context_quick_export" should contain:
      """
      code;description-de_DE-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce
      amor;;"Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR";
      """

  Scenario: Successfully export all columns for quick export product models
    When I sort by "ID" value ascending
    And I select row amor
    And I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_quick_export" quick export to finish
    And I go on the last executed job resume of "csv_product_quick_export"
    Then I should see the text "COMPLETED"
    And second exported file of "csv_product_quick_export" should contain:
      """
      code;brand;care_instructions;categories;collection;description-de_DE-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;erp_name-de_DE;erp_name-en_US;erp_name-fr_FR;family_variant;image;keywords-de_DE;keywords-en_US;keywords-fr_FR;material;meta_description-de_DE;meta_description-en_US;meta_description-fr_FR;meta_title-de_DE;meta_title-en_US;meta_title-fr_FR;name-de_DE;name-en_US;name-fr_FR;notice;parent;price-EUR;price-USD;supplier;wash_temperature;weight;weight-unit
      amor;;;master_men_blazers,supplier_zaro;summer_2016;;"Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR";;;amor;;clothing_colorsize;;;;;;;;;;;;;"Heritage jacket navy";;;;999;;zaro;800;;
      """
