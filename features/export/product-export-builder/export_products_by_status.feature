@javascript
Feature: Export products according to their statuses
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their statuses

  Background:
    Given an "footwear" catalog configuration
    And the following family:
      | code    | requirements-mobile | attributes |
      | rangers | sku,name            | sku,name   |
    And the following products:
      | sku      | enabled | family  | categories        | name-en_US    |
      | SNKRS-1B | 1       | rangers | summer_collection | Black rangers |
      | SNKRS-1R | 0       | rangers | summer_collection | Black rangers |
    And I am logged in as "Julia"

  Scenario: Export products with operator ALL on statuses
    Given I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I filter by "enabled" with operator "" and value "All"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;enabled;family;groups;name-en_US
      SNKRS-1B;summer_collection;1;rangers;;Black rangers
      SNKRS-1R;summer_collection;0;rangers;;Black rangers
      """
