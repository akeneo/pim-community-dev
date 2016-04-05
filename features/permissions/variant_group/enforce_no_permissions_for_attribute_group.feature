Feature: Enforce no permissions for an attribute group in variant group
  In order to be able to prevent some users from viewing some product data
  As a product manager
  I need to be able to enforce no permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | size, color | VARIANT |
    And the following variant group values:
      | group  | attribute | value | locale |
      | SANDAL | name      | bar   | en_US  |
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully hide fields for an attribute group in the variant group form
    Given I am on the "info" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes | IT support |
      | Allowed to view attributes | IT support |
    And I save the attribute group
    When I edit the "SANDAL" variant group
    And I visit the "Attributes" tab
    Then I should not see available attributes Name in group "info"
    And I should not see the Name field

  @javascript
  Scenario: Successfully display read only fields for an attribute group in the variant group form
    Given I am on the "info" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support, Manager |
      | Allowed to edit attributes | IT support |
    And I save the attribute group
    When I edit the "SANDAL" variant group
    And I visit the "Attributes" tab
    Then the fields Name should be disabled
