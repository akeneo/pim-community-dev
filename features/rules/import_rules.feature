Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully import a rule
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
    
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule                        | field | operator | value |
      | canon_beautiful_description | name  | CONTAINS | Canon |
    Then I should see the following rule setter actions:
      | rule                        | field       | value                   |
      | canon_beautiful_description | description | A beautiful description |
