@javascript
Feature: Manage permissions to calculate impacted product by a rule
  In order to allow to calculate impacted products
  As an administrator
  I need to be able to manage permissions to calculate products selected by the rule conditions in rules datagrid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully disable permission to calculate impacted products by a rule
    Given the following product rule definitions:
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
      """
    When I am on the rules page
    And I select row rule_sku
    Then I should see the text "Calculate the affected products"
    When I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I grant rights to group Rules
    And I revoke rights to resource Calculate the affected products for the rules
    And I save the Role
    Then I should not see the text "There are unsaved changes."
    When I am on the rules page
    Then I should not see the text "Calculate the affected products"
