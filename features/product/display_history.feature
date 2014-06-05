@javascript
Feature: Display the product history
  In order to know who, when and what changes has been made to a product
  As a product manager
  I need to have access to a product history

  Scenario: Display product updates
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | sandals-001 |
    And I press the "Save" button in the popin
    And I edit the "sandals-001" product
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value       |
      | 1       | sku      | sandals-001 |
