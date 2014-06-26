@javascript
Feature: Define permissions for an attribute group
  In order to be able to restrict access to some product data
  As Peter
  I need to be able to define permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And I am logged in as "Peter"
    And I am on the "info" attribute group page

  Scenario: Succesfully display the fields for attribute group permissions
    Given I visit the "Permissions" tab
    Then I should see the Permissions to view attributes and Permissions to edit attributes fields

  Scenario: Successfully display editable fields for an attribute group
    Given I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to edit attributes | User, Administrator, Manager |
    And I save the attribute group
    And I edit the "foo" product
    Then I should see the SKU, Name and Manufacturer fields
    When I change the "SKU" to "bar"
    And I change the "Name" to "baz"
    And I change the "Manufacturer" to "Converse"
    Then the product SKU should be "bar"
    Then the product Name should be "baz"
    Then the product Manufacturer should be "Converse"

  Scenario: Successfully disable read-only fields for an attribute group
    Given I visit the "Permissions" tab
    And I fill in the following information:
      | Permissions to view attributes | User, Administrator, Manager |
      | Permissions to edit attributes | User                         |
    And I save the attribute group
    And I edit the "foo" product
    Then I should see the SKU, Name and Manufacturer fields
    And the fields SKU, Name and Manufacturer should be disabled
