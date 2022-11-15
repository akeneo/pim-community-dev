@javascript
Feature: Export products according to multi select values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their multi selected options

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | uuid                                 | sku    | family | name-en_US | weather_conditions | categories      |
      | 0095888e-b214-4e26-97cd-31fa768d5e90 | BOOT-1 | boots  | The boot 1 |                    | 2014_collection |
      | ed5d55c2-1dd5-457d-ad58-bf6dac4b7025 | BOOT-2 | boots  | The boot 2 | dry                | 2014_collection |
      | bd4edc54-ee61-4a0f-99bc-b94576ebfae5 | BOOT-3 | boots  | The boot 3 | dry, wet           | 2014_collection |
      | 77eb825c-63f1-4b96-8df2-bcc31d6c4067 | BOOT-4 | boots  | The boot 4 | snowy              | 2014_collection |
      | 86928657-b225-4e15-9624-14bab8d3537a | BOOT-5 | boots  | The boot 5 | snowy              | 2014_collection |
      | 2f7cc1aa-0002-4e51-a864-524e20c7ee96 | BOOT-6 | boots  | The boot 6 | cold               | 2014_collection |
      | 3ba32633-62a3-4511-b4e7-b09a3eb86680 | BOOT-7 | boots  | The boot 7 |                    | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/footwear_product_export.csv"} |
      | with_uuid | yes                                                                                |

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
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;rating;side_view;size;top_view;weather_conditions
      ed5d55c2-1dd5-457d-ad58-bf6dac4b7025;BOOT-2;;;;1;boots;;;;"The boot 2";;;;;;dry
      bd4edc54-ee61-4a0f-99bc-b94576ebfae5;BOOT-3;;;;1;boots;;;;"The boot 3";;;;;;dry,wet
      2f7cc1aa-0002-4e51-a864-524e20c7ee96;BOOT-6;;;;1;boots;;;;"The boot 6";;;;;;cold
      """
