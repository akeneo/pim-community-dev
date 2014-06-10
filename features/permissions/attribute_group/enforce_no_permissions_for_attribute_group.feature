@javascript
Feature: Enforce no permissions for an attribute group
  In order to be able to prevent some users from viewing some product data
  As Peter
  I need to be able to enforce no permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And I am logged in as "Julia"

  Scenario: Successfully disable no fields for an attribute group in the product edit form
    Given I edit the "foo" product
    Then I should not see the SKU, Name and Manufacturer fields
