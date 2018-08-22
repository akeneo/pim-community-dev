Feature: Keep product asset order during product import
  In order to use ordered product assets in asset collections
  As a product manager
  I would like to order the assets linked to the products in the asset collection

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | type                  | group | reference_data_name |
      | photos | pim_assets_collection | other | assets              |
    And the following family:
      | code | attributes |
      | bags | photos     |
    And the following assets:
      | code   | categories         |
      | bridge | asset_main_catalog |
      | dog    | asset_main_catalog |
      | paint  | asset_main_catalog |

  Scenario: Add ordered assets in a product asset collection through product import
    Given the following products:
      | sku  | family |
      | bag1 | bags   |
      | bag2 | bags   |
    And the following CSV file to import:
      """
      sku;photos
      bag1;dog,bridge,paint
      bag2;paint,bridge,dog
      """
    When the products are imported via the job csv_default_product_import
    Then the asset collection photos of the product bag1 should be ordered as dog, bridge and paint
    And the asset collection photos of the product bag2 should be ordered as paint, bridge and dog

  Scenario: Update ordered product asset collections through product import
    Given the following products:
      | sku  | family | photos           |
      | bag1 | bags   | bridge,dog,paint |
      | bag2 | bags   | bridge,dog,paint |
    And the following CSV file to import:
      """
      sku;photos
      bag1;dog,bridge,paint
      bag2;paint,bridge,dog
      """
    When the products are imported via the job csv_default_product_import
    Then the asset collection photos of the product bag1 should be ordered as dog, bridge and paint
    And the asset collection photos of the product bag2 should be ordered as paint, bridge and dog
