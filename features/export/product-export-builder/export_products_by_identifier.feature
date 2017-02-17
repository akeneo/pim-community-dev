@javascript
Feature: Export products according to their skus
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their skus

  Background:
    Given an "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku,name            | sku,name   |
    And the following products:
      | sku      | enabled | family  | categories        | name-en_US    |
      | SNKRS-1B | 1       | rangers | summer_collection | Black rangers |
      | SNKRS-1R | 1       | rangers | summer_collection | Black rangers |
    And I am logged in as "Julia"

  Scenario: Export products by their identifiers
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                 |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "sku", "operator": "IN", "value": ["SNKRS-1B"]}]} |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;Black rangers
    """
