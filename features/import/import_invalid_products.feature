@javascript
Feature: Import invalid products
  In order to fix incorrect product data
  As a product manager
  I need to know which rows are incorrect and why

  Background: Fail to import malformed prices
    Given the "footwear" catalog configuration
    And the following attributes:
      | label        | type   |
      | Public Price | prices |
    And the following product:
      | sku         | publicPrice |
      | honda-civic |             |
    And the following file to import:
    """
    sku;publicPrice
    renault-kangoo;20000 EUR
    honda-civic;15EUR
    """
    And the following job "footwear_product_import" configuration:
      | filePath          | %file to import% |
      | uploadAllowed     | no               |
      | delimiter         | ;                |
      | enclosure         | "                |
      | escape            | \                |
      | enabled           | yes              |
      | categories column | categories       |
      | family column     | families         |
      | groups column     | groups           |
    And I am logged in as "Julia"

Scenario: Fail to import malformed prices
    Given I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 2 products
    And I should see "Malformed price: \"15EUR\""

  Scenario: Download a file containing invalid products
    Given I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    And the invalid data file of "footwear_product_import" should contain:
    """
    sku;publicPrice
    honda-civic;15EUR

    """
