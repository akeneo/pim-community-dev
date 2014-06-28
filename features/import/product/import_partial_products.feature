@javascript
Feature: Import partial product information
  In order to avoid overwriting existing data
  As a product manager
  I need to be able to import a product without specifying all of its properties

  Scenario: Successfully keep the product family if it is not present in the import file
    Given a "footwear" catalog configuration
    And the following product:
      | sku               | family |
      | caterpillar-boots | boots  |
    And the following file to import:
    """
    sku;name-en_US
    caterpillar-boots;"Caterpillar boots"
    """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 1 product
    And the english name of "caterpillar-boots" should be "Caterpillar boots"
    And family of "caterpillar-boots" should be "boots"
