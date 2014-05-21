@javascript
Feature: Enforce read-only rights for an attribute group
  In order to be able to prevent some users from editing some product data
  As Peter
  I need to be able to enforce read-only rights for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And role "administrator" has the right to view the attribute group "info"
    And I am logged in as "Julia"

  Scenario: Successfully disable read-only fields for an attribute group in the product edit form
    Given I edit the "foo" product
    Then the fields SKU, Name and Manufacturer should be disabled
