@javascript
Feature: Export products with an assets collection
  In order to export a collection of assets
  As a product manager

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export a csv file of products with a collection of assets
    Given the following job "csv_clothing_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following families:
      | code      | label-en_US | attributes       | requirements-tablet | requirements-mobile |
      | my_family | EN label    | sku,name,gallery | sku,name            | sku,name            |
    And the following products:
      | sku  | family    | categories        |
      | pant | my_family | summer_collection |
    And the following product values:
      | product | attribute | value    | locale |
      | pant    | gallery   | dog      |        |
      | pant    | name      | Pant     | en_US  |
      | pant    | name      | Pantalon | fr_FR  |
      | pant    | name      | Hose     | de_DE  |
    And I launched the completeness calculator
    When I am on the "csv_clothing_product_export" export job page
    And I launch the export job
    And I wait for the "csv_clothing_product_export" job to finish
    Then exported file of "csv_clothing_product_export" should contain:
    """
    sku;categories;enabled;family;groups;gallery;name-de_DE;name-en_US;name-fr_FR
    pant;summer_collection;1;my_family;;dog;Hose;Pant;Pantalon
    """
