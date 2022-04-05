@javascript @published-product-feature-enabled
Feature: Export published products
  In order to export validated product data
  As a product manager
  I need to export published products

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Export only the published products updated since the last export
    Given the following job "csv_clothing_mobile_published_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/csv_clothing_mobile_published_product_export.csv                                                                                                           |
      | filters  | {"structure":{"locales":["fr_FR","en_US","de_DE"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_clothing_mobile_published_product_export"}]} |
    And the following products:
      | sku       | family | categories        | price          | size | main_color |
      | tee-white | tees   | winter_collection | 10 EUR, 15 USD | XL   | white      |
      | tee-black | tees   | winter_collection | 10 EUR, 15 USD | XL   | black      |
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
      sku;categories;enabled;family;groups;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;main_color;manufacturer;name-de_DE;name-en_US;name-fr_FR;price-EUR;price-USD;rating;side_view;size
      tee-white;winter_collection;1;tees;;;;;white;;"Weiß t-shirt";"White tee";"Tshirt blanc";10.00;15.00;;;XL
      tee-black;winter_collection;1;tees;;;;;black;;"Schwarz t-shirt";"Black tee";"Tshirt noir";10.00;15.00;;;XL
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
      sku;categories;enabled;family;groups;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;main_color;manufacturer;name-de_DE;name-en_US;name-fr_FR;PACK-groups;PACK-products;PACK-product_models;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;X_SELL-groups;X_SELL-products;X_SELL-product_models
      tee-white;winter_collection;1;tees;;;;;white;;Weiß t-shirt;Tee;Tshirt blanc;;;;10.00;15.00;;;XL;;;;;;;;;
      """
