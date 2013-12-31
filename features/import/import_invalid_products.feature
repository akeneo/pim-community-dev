@javascript
Feature: Import invalid products
  In order to fix incorrect product data
  As Julia
  I need to know which rows are incorrect and why

  Background: Fail to import malformed prices
    Given the "default" catalog configuration
    And the following product:
      | sku         |
      | honda-civic |
    And the following attributes:
      | label        | type   |
      | Public Price | prices |
    And the following product values:
      | product     | attribute   | value |
      | honda-civic | publicPrice |       |
    And the following job:
      | connector            | alias              | code                | label                       | type   |
      | Akeneo CSV Connector | csv_product_import | acme_product_import | Product import for Acme.com | import |
    And the following file to import:
    """
    sku;publicPrice
    renault-kangoo;20000 EUR
    honda-civic;15EUR
    """
    And the following job "acme_product_import" configuration:
      | filePath          | {{ file to import }} |
      | uploadAllowed     | no                   |
      | delimiter         | ;                    |
      | enclosure         | "                    |
      | escape            | \                    |
      | enabled           | yes                  |
      | categories column | categories           |
      | family column     | families             |
      | groups column     | groups               |
    And I am logged in as "Julia"

  Scenario: Fail to import malformed prices
    Given I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 2 products
    And I should see "Malformed price: \"15EUR\""

  Scenario: Download a file containing invalid products
    Given I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    And the invalid data file of "acme_product_import" should contain:
    """
    sku;publicPrice
    honda-civic;15EUR

    """
