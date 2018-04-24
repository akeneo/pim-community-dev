Feature: Import partial product information
  In order to avoid overwriting existing data
  As a product manager
  I need to be able to import a product without specifying all of its properties

  Scenario: Successfully keep the product family if it is not present in the import file
    Given a "footwear" catalog configuration
    And the following product:
      | sku               | family |
      | caterpillar-boots | boots  |
    And the following CSV file to import:
      """
      sku;name-en_US
      caterpillar-boots;"Caterpillar boots"
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 1 product
    And the english localizable value name of "caterpillar-boots" should be "Caterpillar boots"
    And family of "caterpillar-boots" should be "boots"
