@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import valid rule for "boolean" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_boolean:
            conditions:
                - field:    handmade
                  operator: =
                  value:    true
            actions:
                - type:  set
                  field: handmade
                  value: true
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    Then I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_boolean\" as it does not appear to be valid."
    When I am on the "handmade" attribute page
    And I visit the "Rules" tab
    Then I should see the text "true"

  Scenario: Import a copy value rule with valid values for attribute of type boolean in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: handmade
                  to_field:   handmade
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "handmade" attribute page
    And I visit the "Rules" tab
    Then I should see the text "handmade"
    Then I should see the text "is copied into"
