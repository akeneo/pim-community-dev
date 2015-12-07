@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import valid rule for "simple select" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_manufacturer:
            conditions:
                - field:    manufacturer.code
                  operator: IN
                  value:
                      - Volcom
            actions:
                - type:  set_value
                  field: manufacturer
                  value: Desigual
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "created 1"
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_manufacturer\" as it does not appear to be valid."
    When I am on the "manufacturer" attribute page
    And I visit the "Rules" tab
    Then I should see "Desigual"

  Scenario: Import valid rule for "multi select" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_weather:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - dry
            actions:
                - type:  set_value
                  field: weather_conditions
                  value:
                      - dry
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "created 1"
    And I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_weather\" as it does not appear to be valid."
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then I should see "dry"
    And I should not see "wet"

  Scenario: Import a copy value rule with valid values for attribute of type multi select in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy_value
                  from_field: weather_conditions
                  to_field:   weather_conditions
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then I should see "weather_conditions"
    Then I should see "is copied into"

  Scenario: Import a copy value rule with valid values for attribute of type simple select in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy_value
                  from_field: manufacturer
                  to_field:   manufacturer
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "manufacturer" attribute page
    And I visit the "Rules" tab
    Then I should see "manufacturer"
    Then I should see "is copied into"
