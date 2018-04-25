@javascript
Feature: Define permissions for an attribute group with reference data
  In order to be able to restrict access to some product data
  As an administrator
  I need to be able to define permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "boots" family page
    And I visit the "Attributes" tab
    And I add available attributes Sole fabric and Sole color
    And I save the family
    And I logout
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following product:
      | sku | family | sole_color | sole_fabric |
      | foo | boots  | Red        | Nylon       |

  Scenario: Successfully see a reference data attribute
    Given I am logged in as "Mary"
    And I edit the "foo" product
    And I visit the "Other" group
    And I should see the Sole color, Sole fabric fields

  Scenario: Successfully forbidden editable fields for an attribute group which contains references data
    Given I am logged in as "Peter"
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes | IT support, Manager |
    And I save the attribute group
    Then I should not see the text "There are unsaved changes."
    When I logout
    And I am logged in as "Mary"
    And I edit the "foo" product
    And I visit the "Other" group
    But I should see the Sole color and Sole fabric fields

  Scenario: Successfully disable read-only fields for an attribute group which contains references data
    Given I am logged in as "Peter"
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support, Manager |
    And I save the attribute group
    Then I should not see the text "There are unsaved changes."
    When I logout
    And I am logged in as "Mary"
    And I edit the "foo" product
    And I should not see the sole_color and sole_fabric fields
