@javascript
Feature: Export products according to multi select values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their multi selected options

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | weather_conditions | categories      |
      | BOOT-1 | boots  | The boot 1 |                    | 2014_collection |
      | BOOT-2 | boots  | The boot 2 | dry                | 2014_collection |
      | BOOT-3 | boots  | The boot 3 | dry, wet           | 2014_collection |
      | BOOT-4 | boots  | The boot 4 | snowy              | 2014_collection |
      | BOOT-5 | boots  | The boot 5 | snowy              | 2014_collection |
      | BOOT-6 | boots  | The boot 6 | cold               | 2014_collection |
      | BOOT-7 | boots  | The boot 7 |                    | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/footwear_product_export.csv |

  Scenario: Export only the product values with selected options
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Weather conditions
    And I filter by "weather_conditions.code" with operator "In list" and value "Dry,Cold"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-2;;;;1;boots;;;;"The boot 2";;;;;;;dry
      BOOT-3;;;;1;boots;;;;"The boot 3";;;;;;;dry,wet
      BOOT-6;;;;1;boots;;;;"The boot 6";;;;;;;cold
      """

  Scenario: Export all the product values when no option is provided with operator IN LIST
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Weather conditions
    And I filter by "weather_conditions.code" with operator "In list" and value ""
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-1;;;;1;boots;;;;"The boot 1";;;;;;;
      BOOT-2;;;;1;boots;;;;"The boot 2";;;;;;;dry
      BOOT-3;;;;1;boots;;;;"The boot 3";;;;;;;dry,wet
      BOOT-4;;;;1;boots;;;;"The boot 4";;;;;;;snowy
      BOOT-5;;;;1;boots;;;;"The boot 5";;;;;;;snowy
      BOOT-6;;;;1;boots;;;;"The boot 6";;;;;;;cold
      BOOT-7;;;;1;boots;;;;"The boot 7";;;;;;;
      """

  Scenario: Successfully remove multi select filter
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Weather conditions
    And I filter by "weather_conditions.code" with operator "Is empty" and value ""
    And I press the "Save" button
    And I should not see the text "There are unsaved changes"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    When I hide the filter "weather_conditions.code"
    Then I should not see the filter "weather_conditions.code"
