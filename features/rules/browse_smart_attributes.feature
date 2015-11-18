@javascript
Feature: Browse smart attributes in the attribute grid
  In order to know which attributes are smart
  As a regular user
  I need to see and filter by the smart property

  Background:
    Given a "footwear" catalog configuration
    And the following product rules:
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value |
      | rule1 | sku   | =        | foo   |
    And the following product rule setter actions:
      | rule  | field | value | locale |
      | rule1 | name  | Foo   | en_US  |
    And I am logged in as "Julia"

  Scenario: Successfully display the smart column in the attribute grid
    Given I am on the attributes page
    Then I should see the columns Code, Label, Type, Scopable, Localizable, Group and Smart

  Scenario: Successfully filter by the smart property in the attribute grid
    Given I am on the attributes page
    When I filter by "Type" with value "Text"
    Then I should be able to use the following filters:
      | filter | value | result  |
      | Smart  | yes   | name    |
      | Smart  | no    | 123, comment |

  @info https://akeneo.atlassian.net/browse/PIM-5056
  Scenario: Successfully display the correct amount of smart attribute on grid
    Given the following product rule setter actions:
      | rule  | field           | value         | locale | scope  |
      | rule1 | comment         | Foo           |        |        |
      | rule1 | description     | Foo           |en_US   | mobile |
      | rule1 | handmade        | true          |        |        |
      | rule1 | length          | 10 CENTIMETER |        |        |
      | rule1 | price           | 2 EUR         |        |        |
      | rule1 | number_in_stock | 2             |        |        |
      | rule1 | destocking_date | 2015-05-26    |        |        |
    And I am on the attributes page
    And the product rule "rule1" is executed
    When I filter by "Smart" with value "yes"
    Then the grid should contain 8 elements
