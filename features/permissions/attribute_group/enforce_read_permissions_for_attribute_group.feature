@javascript @skip
Feature: Enforce read-only permissions for an attribute group
  In order to be able to prevent some users from editing some product data
  As Peter
  I need to be able to enforce read-only permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And role "administrator" has the permission to view the attribute group "info"
    And I am logged in as "Julia"

  Scenario: Successfully disable read-only fields for an attribute group in the product edit form
    Given I edit the "foo" product
    Then the fields SKU, Name and Manufacturer should be disabled
