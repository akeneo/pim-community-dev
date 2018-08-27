@javascript
Feature: Export published products
  In order to export validated product data
  As a product manager
  I need to export published products

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the missing product asset variations have been generated

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully export published products
    Given I add the "english UK" locale to the "mobile" channel
    And the following locale accesses:
      | locale | user group | access |
      | en_GB  | Manager    | edit   |
    And the following job "csv_clothing_mobile_published_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/csv_clothing_mobile_published_product_export.csv        |
      | filters  | {"structure":{"locales":["fr_FR","en_US","en_GB","de_DE"],"scope":"mobile"},"data":[]} |
    And the following products:
      | sku          | family  | categories                 | price          | size | main_color | manufacturer |
      | jacket-white | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | white      | Volcom       |
      | jacket-black | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | black      | Volcom       |
    And the following product values:
      | product      | attribute   | value                            | locale | scope  |
      | jacket-white | name        | White jacket                     | en_US  |        |
      | jacket-white | name        | White jacket                     | en_GB  |        |
      | jacket-white | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-white | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-black | name        | White jacket                     | en_US  |        |
      | jacket-black | name        | White jacket                     | en_GB  |        |
      | jacket-black | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-black | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-black | gallery     | paint                            |        |        |
      | jacket-white | description | A stylish white jacket           | en_US  | mobile |
      | jacket-white | description | An elegant white jacket          | en_GB  | mobile |
      | jacket-white | description | Un Jacket blanc élégant          | fr_FR  | mobile |
      | jacket-white | description | Ein elegantes weißes Jacket      | de_DE  | mobile |
      | jacket-white | description | A really stylish white jacket    | en_US  | mobile |
      | jacket-white | description | Ein sehr elegantes weißes Jacket | de_DE  | mobile |
      | jacket-white | gallery     | paint                            |        |        |
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akeneo.jpg
    And I save the asset
    And I launched the completeness calculator
    And I edit the "jacket-white" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    And I edit the "jacket-black" product
    When I press the secondary action "Publish"
    And I confirm the publishing
    When I am on the "csv_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_mobile_published_product_export" job to finish
    Then exported file of "csv_clothing_mobile_published_product_export" should contain:
    """
    sku;categories;datasheet;description-de_DE-mobile;description-en_GB-mobile;description-en_US-mobile;description-fr_FR-mobile;enabled;family;gallery;groups;handmade;length;length-unit;main_color;manufacturer;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-mobile;price-EUR;price-USD;rating;release_date-mobile;secondary_color;side_view;size;top_view;weather_conditions
    jacket-white;jackets,winter_collection;;"Ein sehr elegantes weißes Jacket";;"A really stylish white jacket";"Un Jacket blanc élégant";1;jackets;paint;;0;;;white;Volcom;"Weißes Jacket";;"White jacket";"Jacket blanc";;10.00;15.00;;;;;XL;;
    jacket-black;jackets,winter_collection;;;;;;1;jackets;paint;;0;;;black;Volcom;"Weißes Jacket";"White jacket";"White jacket";"Jacket blanc";;10.00;15.00;;;;;XL;;
    """

  Scenario: Export only the published products updated since the last export
    Given the following job "csv_clothing_mobile_published_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/csv_clothing_mobile_published_product_export.csv                                                                                                           |
      | filters  | {"structure":{"locales":["fr_FR","en_US","de_DE"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_clothing_mobile_published_product_export"}]} |
    And the following products:
      | sku       | family | categories        | price          | size | main_color |
      | tee-white | tees   | winter_collection | 10 EUR, 15 USD | XL   | White      |
      | tee-black | tees   | winter_collection | 10 EUR, 15 USD | XL   | Black      |
    And the following product values:
      | product   | attribute | value           | locale |
      | tee-white | name      | White tee       | en_US  |
      | tee-white | name      | Tshirt blanc    | fr_FR  |
      | tee-white | name      | Weiß t-shirt    | de_DE  |
      | tee-black | name      | Black tee       | en_US  |
      | tee-black | name      | Tshirt noir     | fr_FR  |
      | tee-black | name      | Schwarz t-shirt | de_DE  |
    When I edit the "tee-white" product
    And I press the secondary action "Publish"
    And I confirm the publishing
    When I edit the "tee-black" product
    And I press the secondary action "Publish"
    And I confirm the publishing
    When I am on the "csv_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_mobile_published_product_export" job to finish
    Then exported file of "csv_clothing_mobile_published_product_export" should contain:
      """
      sku;categories;enabled;family;groups;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;front_view;main_color;manufacturer;name-de_DE;name-en_US;name-fr_FR;price-EUR;price-USD;rating;side_view;size
      tee-white;winter_collection;1;tees;;;;;;white;;"Weiß t-shirt";"White tee";"Tshirt blanc";10.00;15.00;;;XL
      tee-black;winter_collection;1;tees;;;;;;black;;"Schwarz t-shirt";"Black tee";"Tshirt noir";10.00;15.00;;;XL
      """
    When I edit the "tee-white" product
    And I change the "Name" to "Tee"
    And I save the product
    And I press the secondary action "Publish this version"
    And I confirm the publishing
    When I am on the "csv_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_mobile_published_product_export" job to finish
    Then exported file of "csv_clothing_mobile_published_product_export" should contain:
      """
      sku;categories;enabled;family;groups;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;front_view;main_color;manufacturer;name-de_DE;name-en_US;name-fr_FR;PACK-groups;PACK-products;PACK-product_models;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
      tee-white;winter_collection;1;tees;;;;;;white;;Weiß t-shirt;Tee;Tshirt blanc;;;;10.00;15.00;;;XL;;;;;;;;;
      """

  Scenario: Export only the published products updated since a defined date
    Given the following products:
      | sku       | family | categories        | price          | size | main_color |
      | tee-white | tees   | winter_collection | 10 EUR, 15 USD | XL   | White      |
      | tee-black | tees   | winter_collection | 10 EUR, 15 USD | XL   | Black      |
    And the following product values:
      | product   | attribute | value           | locale |
      | tee-white | name      | White tee       | en_US  |
      | tee-white | name      | Tshirt blanc    | fr_FR  |
      | tee-white | name      | Weiß t-shirt    | de_DE  |
      | tee-black | name      | Black tee       | en_US  |
      | tee-black | name      | Tshirt noir     | fr_FR  |
      | tee-black | name      | Schwarz t-shirt | de_DE  |
    And the following job "csv_clothing_mobile_published_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/csv_clothing_mobile_published_product_export.csv                                                     |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": ">", "value": "2016-04-25 00:00:00"}]} |
    When I edit the "tee-white" product
    And I press the secondary action "Publish"
    And I confirm the publishing
    When I edit the "tee-black" product
    And I press the secondary action "Publish"
    And I confirm the publishing
    When I am on the "csv_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_mobile_published_product_export" job to finish
    Then exported file of "csv_clothing_mobile_published_product_export" should contain:
      """
      sku;categories;enabled;family;groups;description-en_US-mobile;front_view;main_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size
      tee-white;winter_collection;1;tees;;;;white;;"White tee";10.00;15.00;;;XL
      tee-black;winter_collection;1;tees;;;;black;;"Black tee";10.00;15.00;;;XL
      """
    When the following job "csv_clothing_mobile_published_product_export" configuration:
      | filters | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": "<", "value": "2016-04-25 00:00:00"}]} |
    And I am on the "csv_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_mobile_published_product_export" job to finish
    Then exported file of "csv_clothing_mobile_published_product_export" should be empty
