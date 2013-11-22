Feature: Import invalid products
  In order to fix incorrect product data
  As Julia
  I need to know which row are incorrect and why

  Scenario: Fail to import malformed prices
    Given the "default" catalog configuration
    And the following product:
      | sku         |
      | honda-civic |
    And the following product attributes:
      | label        | type   | product     |
      | Public Price | prices | honda-civic |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And the following file to import:
    """
    sku;publicPrice
    honda-civic;15EUR
    """
    And the following job "acme_product_import" configuration:
      | step                | element   | property          | value                |
      | import              | reader    | filePath          | {{ file to import }} |
      | import              | reader    | uploadAllowed     | no                   |
      | import              | reader    | delimiter         | ;                    |
      | import              | reader    | enclosure         | "                    |
      | import              | reader    | escape            | \                    |
      | import              | processor | enabled           | yes                  |
      | import              | processor | categories column | categories           |
      | import              | processor | family column     | families             |
      | import              | processor | groups column     | groups               |
      | import_associations | reader    | filePath          | {{ file to import }} |
      | import_associations | reader    | uploadAllowed     | no                   |
      | import_associations | reader    | delimiter         | ;                    |
      | import_associations | reader    | enclosure         | "                    |
      | import_associations | reader    | escape            | \                    |

    And I am logged in as "Julia"
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 1 product
    And I should see "Malformed price: \"15EUR\""

