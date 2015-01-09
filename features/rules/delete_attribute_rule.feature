@javascript
Feature: Delete a rule
  In order ease the enrichment of the catalog
  As a regular user
  I need to be able to delete a rule

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following product rules:
      | code                 | priority |
      | set_tees_description | 10       |
    And the following product rule conditions:
      | rule                 | field           | operator | value | locale | scope  |
      | set_tees_description | categories.code | IN       | tees  |        |        |
    And the following product rule setter actions:
      | rule                 | field         | value                | locale | scope  |
      | set_tees_description | description   | an other description | fr_FR  | tablet |

  Scenario: Successfully delete rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab
    And I delete the rule "set_tees_description"
    Then I should see "No rule for now"
