@javascript
Feature: Export products according to multi select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their reference data values

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | uuid                                 | sku    | family | name-en_US | sole_fabric            | categories      |
      | fff63ae4-33b5-495d-a0d5-69d14f063520 | HEEL-1 | heels  | The heel 1 | cashmerewool           | 2014_collection |
      | f33a3032-75c7-4287-a876-ea010a6419a9 | HEEL-2 | heels  | The heel 2 | cashmerewool           | 2014_collection |
      | 35000b69-c231-40ba-9bbd-aeac7ba398ff | HEEL-3 | heels  | The heel 3 | cashmerewool, neoprene | 2014_collection |
      | a5d614d3-d41d-4327-8172-e05fb03d8d03 | HEEL-4 | heels  | The heel 4 | neoprene               | 2014_collection |
      | 5d51854e-c17e-4969-bfed-1ea4739b67b8 | HEEL-5 | heels  | The heel 5 | neoprene               | 2014_collection |
      | 05589359-e72c-4a52-9e8a-c0aaf506947f | HEEL-6 | heels  | The heel 6 | silknoil               | 2014_collection |
      | 29e2f8d4-a1e5-41c4-bd07-46fb0818ec2a | HEEL-7 | heels  | The heel 7 | silknoil               | 2014_collection |
      | 0cba361c-0d36-4d0e-9851-f78e27ac2330 | HEEL-8 | heels  | The heel 8 |                        | 2014_collection |
      | 1f16ddb5-93e1-48c9-959c-9833ad42599c | HEEL-9 | heels  | The heel 9 |                        | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/footwear_product_export.csv"} |
      | with_uuid | yes                                                                                |

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
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;heel_color;manufacturer;name-en_US;price-EUR;side_view;size;sole_color;sole_fabric;top_view
      fff63ae4-33b5-495d-a0d5-69d14f063520;HEEL-1;;;;1;heels;;;;"The heel 1";;;;;cashmerewool;
      f33a3032-75c7-4287-a876-ea010a6419a9;HEEL-2;;;;1;heels;;;;"The heel 2";;;;;cashmerewool;
      35000b69-c231-40ba-9bbd-aeac7ba398ff;HEEL-3;;;;1;heels;;;;"The heel 3";;;;;cashmerewool,neoprene;
      05589359-e72c-4a52-9e8a-c0aaf506947f;HEEL-6;;;;1;heels;;;;"The heel 6";;;;;silknoil;
      29e2f8d4-a1e5-41c4-bd07-46fb0818ec2a;HEEL-7;;;;1;heels;;;;"The heel 7";;;;;silknoil;
      """
