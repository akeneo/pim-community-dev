@javascript
Feature: purge product versions
  In order to clean the history list of versions for a product and to lighten the database volume
  As a product manager
  I need to be able to purge the versions and keep the published version from being deleted

  Scenario: Successfully purges product versions but keeps the published version
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family  |
      | jacket | jackets |
    And I am logged in as "Julia"
    And I am on the "jacket" product page
    And I fill in the following information:
      | Name        | Akeneo jacket       |
      | Description | High quality jacket |
    And I visit the "Sizes" group
    And I fill in the following information:
      | Length      | 29 Centimeter       |
    And I save the product
    And I visit the "Product information" group
    And I change the Name to "Top Akeneo jacket"
    And I save the product
    And I press the secondary action "Publish"
    And I confirm the publishing
    And I change the Description to "Very high quality jacket"
    And I save the product
    And I visit the "History" column tab
    Then there should be 4 updates
    When I launch the purge versions command for entity "Pim\Component\Catalog\Model\Product"
    And I am on the "jacket" product page
    And I save the product
    Then there should be 3 updates
