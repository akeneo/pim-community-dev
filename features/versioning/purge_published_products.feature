@javascript
Feature: purge product versions
  In order to clean the history list of versions for a product and to lighten the database volume
  As a product manager
  I need to be able to purge the versions and keep the published version from being deleted

  Scenario: Successfully purges product versions but keeps the published version
    Given a "footwear" catalog configuration
    And the following product:
      | sku     |
      | boots   |
    And I am logged in as "Julia"
    And I am on the "boots" product page
    When I add available attribute Length, Description, Name
    And I fill in the following information:
      | Name        | Akeneo boots       |
      | Description | High quality boots |
      | Length      | 29 Centimeter      |
    And I save the product
    And I change the Name to "Top Akeneo boots"
    And I save the product
    And I press the "Publish" button
    And I confirm the publishing
    And I change the Description to "Very high quality boots"
    And I save the product
    And I open the "History" panel
    Then there should be 4 updates
    When I launch the purge versions command for entity "Pim\\Component\\Catalog\\Model\\Product"
    And I am on the "boots" product page
    And I open the "History" panel
    Then there should be 3 updates
