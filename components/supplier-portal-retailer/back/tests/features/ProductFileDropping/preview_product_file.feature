Feature: Supplier Portal - Product File Dropping - Preview a product file

  Background:
    Given a supplier

  Scenario: A product file can be previewed
    Given a product file
    And the following product file preview:
      | sku      | name      | description             |
      | product1 | Product 1 | Great product           |
      | product2 | Product 2 | Another amazing product |
    When a retailer previews the product file
    Then the retailer should get the following product file preview:
      | sku      | name      | description             |
      | product1 | Product 1 | Great product           |
      | product2 | Product 2 | Another amazing product |

  Scenario: The product file cannot be previewed if it does not exist
    Given a product file
    When a retailer try to preview a product file that does not exist
    Then the retailer should be warned that the product file does not exist
