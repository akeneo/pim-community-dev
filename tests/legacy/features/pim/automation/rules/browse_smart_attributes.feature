@javascript
Feature: Browse smart attributes in the attribute grid
  In order to know which attributes are smart
  As a regular user
  I need to see and filter by the smart property

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display the smart column in the attribute grid
    Given I am on the attributes page
    Then I should see the columns Label, Type, Group, Scopable, Localizable, Smart and Quality

  Scenario Outline: Successfully filter by the smart property in the attribute grid
    Given I am on the attributes page
    And the following product rule definitions:
      """
      rule1:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value: camcorders
        actions:
          - type:  set
            field: name
            value: Foo
            locale: en_US
      """
    And I filter by "type" with operator "equals" and value "Text"
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter | operator | value | result                 | count |
      | smart  | equals   | yes   | Name                   | 1     |
      | smart  | equals   | no    | Attribute 123, Comment | 2     |

  @info https://akeneo.atlassian.net/browse/PIM-5056
  Scenario: Successfully display the correct amount of smart attribute on grid
    Given I am on the attributes page
    And the following product rule definitions:
      """
      rule1:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value: camcorders
        actions:
          - type:  set
            field: name
            value: Foo
            locale: en_US
          - type:  set
            field: comment
            value: Foo
          - type:  set
            field: description
            value: Foo
            locale: en_US
            scope: mobile
          - type:  set
            field: handmade
            value: true
          - type:  set
            field: length
            value:
              amount: 10
              unit: CENTIMETER
          - type:  set
            field: price
            value:
              - amount: 2
                currency: EUR
          - type:  set
            field: number_in_stock
            value: 2
          - type:  set
            field: destocking_date
            value: "2015-05-26"
      """
    When I filter by "smart" with operator "equals" and value "yes"
    Then the grid should contain 8 elements
