@javascript
Feature: Filter product groups
  In order to filter product groups in the catalog
  As a user
  I need to be able to filter product groups in the catalog

  Background:
    Given there is no product group
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following product groups:
      | code           | label         | attributes  | type    |
      | TSHIRT_ORO    | T-Shirt Oro    | size, color | VARIANT |
      | MUG           | Mug Akeneo     | color       | VARIANT |
      | TSHIRT_AKENEO | T-Shirt Akeneo | size        | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the product groups page
    Then I should see the filters Code, Label and Axis
    And the grid should contain 3 elements
    And I should see groups TSHIRT_ORO, MUG and TSHIRT_AKENEO

  Scenario: Successfully filter by code
    Given I am on the product groups page
    When I filter by "Code" with value "MUG"
    Then the grid should contain 1 element
    And I should see group MUG
    And I should not see groups TSHIRT_ORO and TSHIRT_AKENEO

  Scenario: Successfully filter by label
    Given I am on the product groups page
    When I filter by "Label" with value "Akeneo"
    Then the grid should contain 2 elements
    And I should see groups MUG and TSHIRT_AKENEO
    And I should not see group TSHIRT_ORO

  Scenario: Successfully filter by label and code
    Given I am on the product groups page
    When I filter by "Code" with value "TSHIRT"
    And I filter by "Label" with value "Akeneo"
    Then the grid should contain 1 element
    And I should see group TSHIRT_AKENEO
    And I should not see groups MUG and TSHIRT_ORO

  Scenario: Successfully filter by axis
    Given I am on the product groups page
    When I filter by "Axis" with value "Color"
    Then the grid should contain 2 elements
    And I should see group TSHIRT_ORO and MUG
    And I should not see groups TSHIRT_AKENEO
