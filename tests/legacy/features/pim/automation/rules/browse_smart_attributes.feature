@javascript @product-rules-feature-enabled
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
