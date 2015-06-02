@javascript
Feature: Define permissions for an attribute group
  In order to be able to restrict access to some product data
  As an administrator
  I need to be able to define permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And I am logged in as "Peter"
    And I am on the "info" attribute group page

  Scenario: Successfully display the fields for attribute group permissions
    Given I visit the "Permissions" tab
    Then I should see the Allowed to view attributes and Allowed to edit attributes fields

  Scenario: Successfully display editable fields for an attribute group
    Given I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes | IT support, Manager |
    And I save the attribute group
    And I edit the "foo" product
    Then I should see the SKU, Name and Manufacturer fields
    When I change the "SKU" to "bar"
    And I change the "Name" to "baz"
    And I change the "Manufacturer" to "Converse"
    Then the product SKU should be "bar"
    Then the product Name should be "baz"
    Then the product Manufacturer should be "Converse"

  @javascript
  Scenario: Successfully disable read-only fields for an attribute group
    Given I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support, Manager |
      | Allowed to edit attributes | Manager             |
    And I save the attribute group
    And I edit the "foo" product
    Then I should see the SKU, Name and Manufacturer fields
    And the fields SKU, Name and Manufacturer should be disabled
