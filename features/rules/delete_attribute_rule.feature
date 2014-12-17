@javascript
Feature: Delete a rule
  In order ease the enrichment of the catalog
  As a regular user
  I need to be able to delete a rule

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product rules:
      | code          | priority |
      | copy_fallback | 10       |
    And the following product rule conditions:
      | rule          | field              | operator    | value          | locale | scope  |
      | copy_fallback | category           | IN          | men            |        |        |
    And the following product rule setter actions:
      | rule          | field         | value                | locale | scope     |
      | copy_fallback | description   | an other description | fr_FR  | ecommerce |

  Scenario: Successfully show rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab
    And I delete the rule "copy_fallback"
    Then I should see "No rule for now"
