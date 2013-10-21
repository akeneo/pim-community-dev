@javascript
Feature: Filter variants
  In order to filter variants in the catalog
  As a user
  I need to be able to filter variants in the catalog

  Background:
    Given there is no variant
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following variants:
      | code           | label         | attributes  | type    |
      | TSHIRT_ORO    | T-Shirt Oro    | size, color | VARIANT |
      | MUG           | Mug Akeneo     | color       | VARIANT |
      | TSHIRT_AKENEO | T-Shirt Akeneo | size        | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the variants page
    Then I should see the filters Code, Label and Axis
    And the grid should contain 3 elements
    And I should see variants TSHIRT_ORO, MUG and TSHIRT_AKENEO

  Scenario: Successfully filter by code
    Given I am on the variants page
    When I filter by "Code" with value "MUG"
    Then the grid should contain 1 element
    And I should see variant MUG
    And I should not see variants TSHIRT_ORO and TSHIRT_AKENEO

  Scenario: Successfully filter by label
    Given I am on the variants page
    When I filter by "Label" with value "Akeneo"
    Then the grid should contain 2 elements
    And I should see variants MUG and TSHIRT_AKENEO
    And I should not see variant TSHIRT_ORO

  Scenario: Successfully filter by label and code
    Given I am on the variants page
    When I filter by "Code" with value "TSHIRT"
    And I filter by "Label" with value "Akeneo"
    Then the grid should contain 1 element
    And I should see variant TSHIRT_AKENEO
    And I should not see variants MUG and TSHIRT_ORO

  Scenario: Successfully filter by axis
    Given I am on the variants page
    When I filter by "Axis" with value "Color"
    Then the grid should contain 2 elements
    And I should see variant TSHIRT_ORO and MUG
    And I should not see variants TSHIRT_AKENEO
