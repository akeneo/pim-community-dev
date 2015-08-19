@javascript
Feature: Export products with an assets collection
  In order to export a collection of assets
  As a product manager

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export a csv file of products with a collection of assets
    Given the following job "clothing_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following products:
      | sku  | family | categories        | price          | size | name-en_US | main_color |
      | pant | pants  | summer_collection | 50 EUR, 70 USD | S    | Model 1    | white      |
    And the following product values:
      | product | attribute | value    | locale |
      | pant    | gallery   | dog      |        |
      | pant    | name      | Pant     | en_US  |
      | pant    | name      | Pantalon | fr_FR  |
      | pant    | name      | Hose     | de_DE  |
    And I launched the completeness calculator
    When I am on the "clothing_product_export" export job page
    And I launch the export job
    And I wait for the "clothing_product_export" job to finish
    Then exported file of "clothing_product_export" should contain:
    """
    sku;categories;enabled;family;gallery;groups;main_color;name-de_DE;name-en_US;name-fr_FR;price-EUR;price-USD;size
    pant;summer_collection;1;pants;dog;;white;Hose;Pant;Pantalon;50.00;70.00;S
    """
