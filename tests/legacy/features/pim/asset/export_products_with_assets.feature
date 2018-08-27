Feature: Keep product asset order during product export
  In order to use ordered product assets in asset collections
  As a product manager
  I would like to export products with ordered asset collections

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
    And the following products:
      | sku  | family | photos           |
      | bag1 | bags   | dog,bridge,paint |
      | bag2 | bags   | paint,bridge,dog |

  Scenario: Export products with ordered asset collections
    Given the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "ecommerce"},"data":[{"field":"completeness","operator":"ALL","value":""}]} |
    When the products are exported via the job csv_product_export
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;photos
      bag1;;1;bags;;dog,bridge,paint
      bag2;;1;bags;;paint,bridge,dog
      """

  Scenario: Export published products with ordered asset collections
    Given the following jobs:
      | connector            | type   | alias                        | code                         | label                        |
      | Akeneo CSV Connector | export | csv_published_product_export | csv_published_product_export | CSV published product export |
    And the following job "csv_published_product_export" configuration:
      | filePath | %tmp%/product_export/published_product_export.csv                                                                         |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "ecommerce"},"data":[{"field":"completeness","operator":"ALL","value":""}]} |
    And I publish the product "bag1"
    When the published products are exported via the job csv_published_product_export
    Then exported file of "csv_published_product_export" should contain:
      """
      sku;categories;enabled;family;groups;photos
      bag1;;1;bags;;dog,bridge,paint
      """

  Scenario: Export products with ordered asset collections
    Given the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "ecommerce"},"data":[{"field":"completeness","operator":"ALL","value":""}]} |
    When the products are exported via the job csv_product_export
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;enabled;family;groups;photos
      bag1;;1;bags;;dog,bridge,paint
      bag2;;1;bags;;paint,bridge,dog
      """

  Scenario: Export published products with ordered asset collections
    Given the following jobs:
      | connector            | type   | alias                        | code                         | label                        |
      | Akeneo CSV Connector | export | csv_published_product_export | csv_published_product_export | CSV published product export |
    And the following job "csv_published_product_export" configuration:
      | filePath | %tmp%/product_export/published_product_export.csv                                                                         |
      | filters  | {"structure": {"locales": ["en_US"], "scope": "ecommerce"},"data":[{"field":"completeness","operator":"ALL","value":""}]} |
    And I publish the product "bag1"
    When the published products are exported via the job csv_published_product_export
    Then exported file of "csv_published_product_export" should contain:
      """
      sku;categories;enabled;family;groups;photos
      bag1;;1;bags;;dog,bridge,paint
      """
