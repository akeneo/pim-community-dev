Feature: Import invalid products
  In order to fix incorrect product data
  As Julia
  I need to know which rows are incorrect and why

  Scenario: Fail to import malformed prices
    Given the "default" catalog configuration
    And the following attributes:
      | label        | type   |
      | Public Price | prices |
    And the following product:
      | sku         | publicPrice |
      | honda-civic |             |
    And the following job:
      | connector            | alias              | code                | label                       | type   |
      | Akeneo CSV Connector | csv_product_import | acme_product_import | Product import for Acme.com | import |
    And the following file to import:
    """
    sku;publicPrice
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
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 1 product
    And I should see "Malformed price: \"15EUR\""
