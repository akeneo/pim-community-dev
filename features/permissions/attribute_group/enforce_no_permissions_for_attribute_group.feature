Feature: Enforce no permissions for an attribute group
  In order to be able to prevent some users from viewing some product data
  As a product manager
  I need to be able to enforce no permissions for attribute groups

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku | family  |
      | foo | jackets |
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully hide fields for an attribute group in the product edit form
    Given I am on the "info" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes | IT support |
      | Allowed to view attributes | IT support |
    And I save the attribute group
    And I edit the "foo" product
    Then I should not see the SKU, Name and Manufacturer fields
