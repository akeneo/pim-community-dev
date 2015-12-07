@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import valid rule for "text" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        sony_beautiful_name:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set_value
                  field: name
                  value: My new Super Name
                  locale: en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"sony_beautiful_name\" as it does not appear to be valid."
    When I am on the "name" attribute page
    And I visit the "Rules" tab
    Then I should see "My new Super Name"

  Scenario: Import valid rule for "textarea" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Another good description
                  locale:   fr_FR
                  scope:    tablet
            actions:
                - type:   set_value
                  field:  description
                  value:  My new description
                  locale: en_US
                  scope:  tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    And I should see "created 1"
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "My new description"

  Scenario: Import a copy value rule with valid values for attribute of type textarea in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy_value
                  from_field:  description
                  to_field:    description
                  from_scope:  mobile
                  to_scope:    tablet
                  from_locale: en_US
                  to_locale:   en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "description"
    Then I should see "mobile"
    Then I should see "is copied into"
    Then I should see "description"
    Then I should see "tablet"

  Scenario: Import a copy value rule with valid values for attribute of type text and text in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy_value
                  from_field:  name
                  to_field:    name
                  from_locale: en_US
                  to_locale:   en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "name" attribute page
    And I visit the "Rules" tab
    Then I should see "description"
    Then I should see "en"
    Then I should see "is copied into"
