@javascript
Feature: Browse products selected in rules datagrid
  In order to precisely configure my rules
  As a regular user
  I need to see the number of products selected by the rule conditions in rules datagrid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku      | rating | weight   | size |
      | rangers  | 5      | 650 GRAM | 37   |
      | loafer   | 4      | 745 GRAM | 38   |
      | sneakers | 1      | 850 GRAM | 39   |
    And the following product rule definitions:
      """
      rule_star:
        priority: 10
        conditions:
          - field: rating
            operator: IN
            value:
              - 4
              - 5
        actions:
          - type:  set
            field: name
            value: Star
            locale: en_US
      rule_weight:
        priority: 10
        conditions:
          - field: weight
            operator: >=
            value:
              data: 750
              unit: GRAM
        actions:
          - type:  set
            field: name
            value: Weight
            locale: en_US
      rule_size:
        priority: 10
        conditions:
          - field: size
            operator: IN
            value:
              - 40
        actions:
          - type:  set
            field: name
            value: Size
            locale: en_US
      """

  Scenario: Successfully display rules which has not been executed
    Given I am on the rules page
    Then the row "rule_star" should contain:
      | column             | value                       |
      | Selected products  | Rule has no been calculated |
