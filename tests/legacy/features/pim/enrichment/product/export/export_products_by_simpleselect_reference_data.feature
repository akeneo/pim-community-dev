@javascript
Feature: Export products according to simple select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to its selected reference data value

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | uuid                                 | sku    | family | name-en_US | heel_color | categories      |
      | 26f1eefe-5f36-4570-adc2-64d49a02ec30 | HEEL-1 | heels  | The heel 1 | quartz     | 2014_collection |
      | 4633bfda-f021-4abf-96fe-b9a964f14bbb | HEEL-2 | heels  | The heel 2 | purpureus  | 2014_collection |
      | c21f97c8-ffca-4527-8b72-224fe742f8ad | HEEL-3 | heels  | The heel 3 |            | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/footwear_product_export.csv"} |
      | with_uuid | yes                                                                                |

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
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;side_view;size;sole_color;sole_fabric;top_view
      26f1eefe-5f36-4570-adc2-64d49a02ec30;HEEL-1;;;;1;heels;;quartz;;"The heel 1";;;;;;
      4633bfda-f021-4abf-96fe-b9a964f14bbb;HEEL-2;;;;1;heels;;purpureus;;"The heel 2";;;;;;
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
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;side_view;size;sole_color;sole_fabric;top_view
      26f1eefe-5f36-4570-adc2-64d49a02ec30;HEEL-1;;;;1;heels;;quartz;;"The heel 1";;;;;;
      4633bfda-f021-4abf-96fe-b9a964f14bbb;HEEL-2;;;;1;heels;;purpureus;;"The heel 2";;;;;;
      c21f97c8-ffca-4527-8b72-224fe742f8ad;HEEL-3;;;;1;heels;;;;"The heel 3";;;;;;
      """
