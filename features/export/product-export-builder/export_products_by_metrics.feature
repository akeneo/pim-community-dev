@javascript
Feature: Export products according to metric attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to metric attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile |
      | rangers | sku, name           |
    And the following products:
      | sku      | enabled | family  | categories        | length        |
      | SNKRS-1B | 1       | rangers | summer_collection | 10 CENTIMETER |
      | SNKRS-1R | 1       | rangers | summer_collection | 20 CENTIMETER |
      | SNKRS-1N | 1       | rangers | summer_collection |               |
    And I am logged in as "Julia"

  Scenario: Export products by their metric values without using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "length", "operator": "<", "value": {"data": 15, "unit": "CENTIMETER"}}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;length;length-unit
    SNKRS-1B;summer_collection;1;rangers;;10;CENTIMETER
    """

  Scenario: Export products by their metric values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Length
    And I filter by "length" with operator "Is equal to" and value "20 Centimeter"
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;length;length-unit
    SNKRS-1R;summer_collection;1;rangers;;20;CENTIMETER
    """

  Scenario: Export products with empty metric values using the UI
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Length
    Then I filter by "length" with operator "Is empty" and value ""
    And I press "Save"
    And I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;length;length-unit
    SNKRS-1N;summer_collection;1;rangers;;;
    """
