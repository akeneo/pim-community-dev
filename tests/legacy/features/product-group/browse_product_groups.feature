@javascript
Feature: Browse product groups
  In order to list the existing product groups for the catalog
  As a product manager
  I need to be able to see product groups

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | multi | Multi       | pim_catalog_multiselect  | other |
      | color | Color       | pim_catalog_simpleselect | other |
      | size  | Size        | pim_catalog_simpleselect | other |
    And the following product groups:
      | code         | label-en_US | type   |
      | CROSS_SELL_1 | Cross Sell  | X_SELL |
      | CROSS_SELL_2 | Relational  | X_SELL |
    And I am logged in as "Julia"
    And I am on the product groups page
    Then the grid should contain 2 elements
    And I should see the columns Label and Type
    And I should see groups Cross Sell and Relational
    And the rows should be sorted ascending by Label

  Scenario: Successfully sort product groups
    And I should be able to sort the rows by Label and Type

  Scenario Outline: Successfully filter product groups
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter | operator | value      | result                    | count |
      | type   |          | Cross sell | Cross Sell and Relational | 2     |

  Scenario: Successfully search on label
    When I search "Cross"
    Then the grid should contain 1 element
    Then I should see entity Cross Sell
