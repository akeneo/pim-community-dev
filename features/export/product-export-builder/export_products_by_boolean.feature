@javascript
Feature: Export products according to boolean attribute filter
  In order to export specific products
  As a product manager
  I need to be able to export the products according to boolean attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku,name            | sku,name   |
    And the following products:
      | sku      | enabled | family  | categories        | handmade |
      | SNKRS-1B | 1       | rangers | summer_collection | 0        |
      | SNKRS-1R | 1       | rangers | summer_collection | 1        |
    And I am logged in as "Julia"

  Scenario: Export products by boolean values
    Given the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    When I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Handmade
    And I filter by "handmade" with operator "" and value "No"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;handmade;name-en_US
    SNKRS-1B;summer_collection;1;rangers;;0;
    """
