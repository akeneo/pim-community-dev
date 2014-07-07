@javascript
Feature: Apply permissions for an attribute group when mass edit common attributes
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to mass edit only attributes I have access

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                          |
      | high_heels | sku, name, manufacturer, description, price, rating |
    And the following attribute group accesses:
      | attribute group | role    | access |
      | info            | Manager | edit   |
      | marketing       | Manager | view   |
    And the following product:
      | sku       | family     |
      | highheels | high_heels |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully display only attributes I have edit permissions access
    Given I mass-edit products highheels
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should not see available attributes SKU in group "Product information"
    And I should not see available attributes Price and Rating in group "Marketing"
