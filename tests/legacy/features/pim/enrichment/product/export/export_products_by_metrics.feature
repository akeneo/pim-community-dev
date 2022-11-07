@javascript
Feature: Export products according to metric attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to metric attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code  | type               | label-en_US  | group | metric_family | default_metric_unit | decimals_allowed | negative_allowed |
      | width | pim_catalog_metric | Width        | other | Length        | CENTIMETER          | 0                | 0                |
    And the following family:
      | code    | requirements-mobile | attributes       |
      | rangers | sku                 | sku,length,width     |
    And the following products:
      | uuid                                 | sku      | enabled | family  | categories        | length        | width         |
      | 2d45f137-edc5-4d5c-af94-f95e63fbdd12 | SNKRS-1B | 1       | rangers | summer_collection | 10 CENTIMETER | 15 CENTIMETER |
      | cf7131c7-6a86-42b6-8143-3b39abd5fd0f | SNKRS-1R | 1       | rangers | summer_collection | 20 CENTIMETER |               |
      | bc043daf-e798-4912-b013-dc1e4371b50b | SNKRS-1N | 1       | rangers | summer_collection |               |               |
    And I am logged in as "Julia"

  Scenario: Export products by their metric values
    Given the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"} |
      | with_uuid | yes                                                                       |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Width
    And I add available attributes Length
    Then I filter by "width" with operator "Is empty" and value ""
    And I filter by "length" with operator "Is equal to" and value "20 Centimeter"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    uuid;sku;categories;enabled;family;groups;length;length-unit;width;width-unit
    cf7131c7-6a86-42b6-8143-3b39abd5fd0f;SNKRS-1R;summer_collection;1;rangers;;20;CENTIMETER;;CENTIMETER
    """
