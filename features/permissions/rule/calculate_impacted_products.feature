Feature: Manage permissions to calculate impacted product by a rule
  In order to allow to calculate impacted products
  As an administrator
  I need to be able to manage permissions to calculate products selected by the rule conditions in rules datagrid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  @javascript
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
    Then I should see the text "Calculate the matching products for the rules"
    When I am on the "Administrator" userRole page
    And I visit the "Permissions" tab
    And I click on the "Rules" ACL group
    And I click on the "Calculate the matching products for the rules" ACL role
    And I save the userRole
    When I am on the rules page
    Then I should not see the text "Calculate the matching products for the rules"
