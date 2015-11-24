@javascript
Feature: Delete a rule
  In order ease the enrichment of the catalog
  As a regular user
  I need to be able to delete a rule

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @deprecated
  Scenario: Successfully delete rules of an attribute
    Given the following product rule definitions:
      """
      set_tees_description:
        priority: 10
        conditions:
          - field:    categories.code
            operator: IN
            value:
              - tees
        actions:
          - type:  set_value
            field: description
            value: an other description
            locale: fr_FR
            scope: tablet
      """
    And I am on the "description" attribute page
    And I visit the "Rules" tab
    And I delete the rule "set_tees_description"
    And I should see "Delete this rule"
    And I should see "Are you sure you want to delete this rule? It is not possible to undo this action"
    And I confirm the deletion
    Then I should see "No rule for now"
