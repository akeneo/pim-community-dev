@javascript
Feature: Export products according to price attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to price attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile |
      | rangers | sku, name           |
    And the following products:
      | sku      | enabled | family  | categories        | price          |
      | SNKRS-1B | 1       | rangers | summer_collection | 20 EUR, 30 USD |
      | SNKRS-1R | 1       | rangers | summer_collection | 25 EUR, 40 USD |
      | SNKRS-1N | 1       | rangers | summer_collection |                |
    And I am logged in as "Julia"

  Scenario: Successfully export products by their price values without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "price", "operator": ">", "value": {"data": 20, "currency": "EUR"}}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;price-EUR;price-USD
    SNKRS-1R;summer_collection;1;rangers;;25.00;40.00
    """

  Scenario: Successfully export products by their price values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    When I add available attributes Price
    And I filter by "price" with operator "=" and value "30 USD"
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;price-EUR;price-USD
    SNKRS-1B;summer_collection;1;rangers;;20.00;30.00
    """

  Scenario: Successfully export products with empty price values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    When I add available attributes Price
    And I filter by "price" with operator "Empty" and value ""
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;price-EUR;price-USD
    SNKRS-1N;summer_collection;1;rangers;;;
    """
