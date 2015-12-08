@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import a copy value rule with valid values for attribute of type media in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy_value
                  from_field:  side_view
                  to_field:    side_view
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "side_view"
    Then I should see "is copied into"

  Scenario: Import valid rule for "media" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_media:
            conditions:
              - field:    side_view
                operator: =
                value:    akeneo.jpg
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       filePath:         %fixtures%/akeneo.jpg
                       originalFilename: akeneo.jpg
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_media\" as it does not appear to be valid."
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "akeneo.jpg"
