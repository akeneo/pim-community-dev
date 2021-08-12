@javascript
Feature: Execute rules from the user interface
  In order to run the rules
  As a product manager
  I need to be able to launch their execution from the "Settings/Rules" screen

  Background:
    Given the "footwear" catalog configuration
    And the following product rule definitions:
      """
      rule_sku:
        priority: 10
        labels:
          en_US: Rule sku
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
        labels:
          en_US: Rule weight
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
        labels:
          en_US: Rule size
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
        labels:
          en_US: big size
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
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-6438
  Scenario: Successfully calculate the impacted products on a selection of rules from the user interface
    Given I am on the rules page
    When I select rows "Rule sku", "Rule weight"
    And I press the "Calculate the impacted products" button
    Then I should see the text "Confirm calculation"
    When I confirm the rules calculation
    And I wait for the "rule_impacted_product_count" job to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                     |
      | success | Calculation of the impacted products for the rules finished |
    When I click on the notification "Calculation of the impacted products for the rules finished"
    Then I should see the text "Execution details - Calculation the affected products for the rules [rule_impacted_product_count]"
    And I should see the text "COMPLETED"

  Scenario: Successfully mass execute a selection of rules from the user interface when filtering on label
    Given I am on the rules page
    When I search "Rule"
    And I select rows "Rule sku", "Rule weight"
    And I press the "Execute" button
    Then I should see the text "Confirm execution"
    When I confirm the rules execution
    And I wait for the "rule_engine_execute_rules" job to finish
    And I am on the dashboard page
    And I should have 1 new notification
    Then I should see notification:
      | type    | message                           |
      | success | Execution of the rule(s) complete |
    When I click on the notification "Execution of the rule(s) complete"
    Then I should see the text "Execution details - Rules execution"
    And I should see the text "COMPLETED"
