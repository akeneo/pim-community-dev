@javascript
Feature: Execute rules from the user interface
  In order to run the rules
  As a product manager
  I need to be able to launch their execution from the "Settings/Rules" screen

  Background:
    Given the "clothing" catalog configuration
    And the following product rule definitions:
      """
      copy_description:
        conditions:
          - field:    name
            operator: =
            value:    My nice tshirt
            locale:   en_US
        actions:
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
      update_tees_collection:
        conditions:
          - field:    categories
            operator: IN
            value:
              - tees
        actions:
          - type:   set
            field:  description
            value:  Une belle description
            locale: fr_FR
            scope:  mobile
      """
    And I am logged in as "Julia"

  Scenario: Successfully execute all rules from the user interface
    Given I am on the rules page
    When I press the "Execute rules" button
    Then I should see the text "Confirm execution"
    When I confirm the rules execution
    And I am on the rules page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                           |
      | success | Execution of the rule(s) finished |

  Scenario: Successfully execute one rule from the rule datagrid
    Given I am on the rules page
    When I click on the "Execute" action of the row which contains "copy_description"
    Then I should see the text "Confirm execution"
    When I confirm the rule execution
    And I refresh current page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                           |
      | success | Execution of the rule(s) finished |

  Scenario: Successfully do not execute
    Given I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Execute rules
    And I save the role
    And I should not see the text "There are unsaved changes."
    When I am on the rules page
    Then I should not see the "Execute rules" button
    And I should not be able to view the "Execute" action of the row which contains "copy_description"
