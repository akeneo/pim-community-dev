@javascript
Feature: Export products according to multi select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their reference data values

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | sole_fabric            | categories      |
      | HEEL-1 | heels  | The heel 1 | cashmerewool           | 2014_collection |
      | HEEL-2 | heels  | The heel 2 | cashmerewool           | 2014_collection |
      | HEEL-3 | heels  | The heel 3 | cashmerewool, neoprene | 2014_collection |
      | HEEL-4 | heels  | The heel 4 | neoprene               | 2014_collection |
      | HEEL-5 | heels  | The heel 5 | neoprene               | 2014_collection |
      | HEEL-6 | heels  | The heel 6 | silknoil               | 2014_collection |
      | HEEL-7 | heels  | The heel 7 | silknoil               | 2014_collection |
      | HEEL-8 | heels  | The heel 8 |                        | 2014_collection |
      | HEEL-9 | heels  | The heel 9 |                        | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/footwear_product_export.csv |

  Scenario: Export only the product values with multiple selected reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Sole fabric
    And I filter by "sole_fabric.code" with operator "In list" and value "Cashmerewool,SilkNoil"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;price-USD;side_view;size;sole_color;sole_fabric;top_view
      HEEL-1;;;;1;heels;;;;"The heel 1";;;;;;cashmerewool;
      HEEL-2;;;;1;heels;;;;"The heel 2";;;;;;cashmerewool;
      HEEL-3;;;;1;heels;;;;"The heel 3";;;;;;cashmerewool,neoprene;
      HEEL-6;;;;1;heels;;;;"The heel 6";;;;;;silknoil;
      HEEL-7;;;;1;heels;;;;"The heel 7";;;;;;silknoil;
      """
