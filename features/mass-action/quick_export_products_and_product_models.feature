@javascript
Feature: Export products and product models
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products and product models to several channels

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"

  Scenario: Successfully export only products to multiple channels
    And I select rows model-tshirt-divided-crimson-red, running-shoes-m-crimson-red and tshirt-unique-size-crimson-red
    And I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    When I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And I should see the text "skipped 1"
    And the name of the exported file of "csv_product_grid_context_quick_export" should be "products_export_grid_context_en_US_ecommerce.csv"
    And exported file of "csv_product_grid_context_quick_export" should contain:
      """
      sku;enabled;family;groups;image
      tshirt-unique-size-crimson-red;1;clothing;;
      running-shoes-m-crimson-red;1;shoes;;
      """
