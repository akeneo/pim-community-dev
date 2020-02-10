@javascript
Feature: Export products according to simple select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to its selected reference data value

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | heel_color | categories      |
      | HEEL-1 | heels  | The heel 1 | quartz     | 2014_collection |
      | HEEL-2 | heels  | The heel 2 | purpureus  | 2014_collection |
      | HEEL-3 | heels  | The heel 3 |            | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/footwear_product_export.csv |

  Scenario: Export only the product values with selected reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Heel color
    And I filter by "heel_color.code" with operator "In list" and value "Quartz,Purpureus"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;price-USD;side_view;size;sole_color;sole_fabric;top_view
      HEEL-1;;;;1;heels;;quartz;;"The heel 1";;;;;;;
      HEEL-2;;;;1;heels;;purpureus;;"The heel 2";;;;;;;
      """

  Scenario: Export all the product values when no reference data is provided
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Heel color
    And I filter by "heel_color.code" with operator "In list" and value ""
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;price-USD;side_view;size;sole_color;sole_fabric;top_view
      HEEL-1;;;;1;heels;;quartz;;"The heel 1";;;;;;;
      HEEL-2;;;;1;heels;;purpureus;;"The heel 2";;;;;;;
      HEEL-3;;;;1;heels;;;;"The heel 3";;;;;;;
      """
