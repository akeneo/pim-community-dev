@javascript
Feature: Filter product groups
  In order to filter product groups in the catalog
  As a user
  I need to be able to filter product groups in the catalog

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label | type                     |
      | color | Color | pim_catalog_simpleselect |
      | size  | Size  | pim_catalog_simpleselect |
    And the following product groups:
      | code         | label      | attributes | type    |
      | MUG          | Mug Akeneo | color      | VARIANT |
      | CROSS_SELL_1 | Cross Sell |            | X_SELL  |
      | CROSS_SELL_2 | Relational |            | X_SELL  |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the product groups page
    Then I should see the filters Code, Label and Type
    And the grid should contain 2 elements
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And I should not see groups MUG

  Scenario: Successfully filter by code
    Given I am on the product groups page
    When I filter by "Code" with value "1"
    Then the grid should contain 1 element
    And I should see groups CROSS_SELL_1
    And I should not see groups MUG and CROSS_SELL_2

  Scenario: Successfully filter by label
    Given I am on the product groups page
    When I filter by "Label" with value "Cross"
    Then the grid should contain 1 element
    And I should see groups CROSS_SELL_1
    And I should not see groups CROSS_SELL_2 and MUG

  Scenario: Successfully filter by label and code
    Given I am on the product groups page
    When I filter by "Code" with value "CROSS"
    And I filter by "Label" with value "Relational"
    Then the grid should contain 1 element
    And I should see groups CROSS_SELL_2
    And I should not see groups MUG and CROSS_SELL_1

  Scenario: Successfully filter by type
    Given I am on the product groups page
    When I filter by "Type" with value "X_SELL"
    Then the grid should contain 2 elements
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And I should not see groups MUG
