@javascript
Feature: Browse products selected in rules datagrid
  In order to precisely configure my rules
  As a regular user
  I need to see the number of products selected by the rule conditions in rules datagrid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku      | sku      | weight   | size |
      | rangers  | POJML-45 | 650 GRAM | 37   |
      | loafer   | POJML-25 | 745 GRAM | 38   |
      | sneakers | POL-45   | 850 GRAM | 39   |
    And the following product rule definitions:
      """
      rule_sku:
        priority: 10
        conditions:
          - field: sku
            operator: CONTAINS
            value: POJML
        actions:
          - type:  set
            field: name
            value: PIJML
            locale: en_US
      rule_weight:
        priority: 10
        conditions:
          - field: weight
            operator: ">="
            value:
              amount: 750
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
      rule_big_size:
        priority: 10
        conditions:
          - field: size
            operator: IN
            value:
              - 60
        actions:
          - type:  set
            field: name
            value: Big size
            locale: en_US
      """

  Scenario: Successfully display the number of matching products by the rule conditions
    Given I am on the rules page
    When I select rows rule_sku, rule_weight and rule_size
    And I press the "Calculate the affected products" button
    Then I should see the text "Calculation confirmation"
    When I confirm the execution
    And I wait for the "rule_impacted_product_count" quick export to finish
    Then I should see the text "Number of rules 3"
    When I am on the rules page
    Then I should see notification:
      | type    | message                                                     |
      | success | Calculation of the affected products for the rules finished |
    And the row "rule_sku" should contain:
      | column            | value               |
      | Affected products | 2 affected products |
    And the row "rule_weight" should contain:
      | column            | value              |
      | Affected products | 1 affected product |
    And the row "rule_size" should contain:
      | column            | value              |
      | Affected products | 0 affected product |
    And the row "rule_big_size" should contain:
      | column            | value              |
      | Affected products | Not yet calculated |
