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

  @javascript
  Scenario: Fails when import rule with missing conditions key
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            wrong:
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
    And I should see "Rule content \"canon_beautiful_description\" should have a \"conditions\" key."

  @javascript
  Scenario: Fails when import rule with missing actions key
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            wrong:
                - type:  set_value
                  field: description
                  value: A beautiful description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "Rule content \"canon_beautiful_description\" should have a \"actions\" key."

  @javascript
  Scenario: Fails when import rule with missing operator key for conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:       name
                  wrong_value: CONTAINS
                  value:       Canon
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
    And I should see "conditions[0].operator: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing field key for conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - wrong_field: name
                  operator:    CONTAINS
                  value:       Canon
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
    And I should see "conditions[0].field: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing from_field key for copy action
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:     copy_value
                  to_field: camera_model_name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "actions[0].fromField: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing to_field key for copy action
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:       copy_value
                  from_field: camera_model_name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "actions[0].toField: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing value key for conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
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
    And I should see "conditions[0].value: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing value key for set action
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
                  wrong: A beautiful description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "actions[0].value: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing value key for set action
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
    And I should see "condition[0].locale: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with invalid operator for conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: WRONG
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
    And I should see "wrong operator"

#  @javascript
#  Scenario: Fails when import rule with wrong locale key for set action
#    Given the following yaml file to import:
#    """
#    rules:
#        canon_beautiful_description:
#            conditions:
#                - field:    name
#                  operator: CONTAINS
#                  value:    Canon
#                  scope:    []
#            actions:
#                - type:  set_value
#                  field: description
#                  value: A beautiful description
#
#    """
#    And the following job "clothing_rule_import" configuration:
#      | filePath | %file to import% |
#    When I am on the "clothing_rule_import" import job page
#    And I launch the import job
#    And I wait for the "clothing_rule_import" job to finish
#    And I should see "condition[0].locale: This value should not be blank."

  @javascript
  Scenario: Fails when import rule with missing type key for copy or set action
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - wrong: set_value
                  field: description
                  value: A beautiful description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "Rule content \"canon_beautiful_description\" has an action with no type."

  @javascript
  Scenario: Fails when import rule with invalid type for copy or set action
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  wrong
                  field: description
                  value: A beautiful description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "Rule \"canon_beautiful_description\" has an unknown type of action \"wrong\"."

#  @javascript
#  Scenario: Fails when import rule with not existing field for conditions
#    Given the following yaml file to import:
#    """
#    rules:
#        canon_beautiful_description:
#            conditions:
#                - field:    wrong
#                  operator: CONTAINS
#                  value:    Canon
#            actions:
#                - type:  set_value
#                  field: description
#                  value: A beautiful description
#
#    """
#    And the following job "clothing_rule_import" configuration:
#      | filePath | %file to import% |
#    When I am on the "clothing_rule_import" import job page
#    And I launch the import job
#    And I wait for the "clothing_rule_import" job to finish
#    And I should see "actions[0].value: This value should not be blank."

#  @javascript
#  Scenario: Fails when import rule with not existing field for set action
#    Given the following yaml file to import:
#    """
#    rules:
#        canon_beautiful_description:
#            conditions:
#                - field:    name
#                  operator: CONTAINS
#                  value:    Canon
#            actions:
#                - type:  set_value
#                  field: wrong
#                  value: A beautiful description
#
#    """
#    And the following job "clothing_rule_import" configuration:
#      | filePath | %file to import% |
#    When I am on the "clothing_rule_import" import job page
#    And I launch the import job
#    And I wait for the "clothing_rule_import" job to finish
#    And I should see "actions[0].value: This value should not be blank."

#  @javascript
#  Scenario: Fails when import rule with not existing from_field for copy action
#    Given the following yaml file to import:
#    """
#    rules:
#        canon_beautiful_description:
#            conditions:
#                - field:    name
#                  operator: CONTAINS
#                  value:    Canon
#            actions:
#                - type:       copy_value
#                  from_field: wrong
#                  to_field:   description
#
#    """
#    And the following job "clothing_rule_import" configuration:
#      | filePath | %file to import% |
#    When I am on the "clothing_rule_import" import job page
#    And I launch the import job
#    And I wait for the "clothing_rule_import" job to finish
#    And I should see "actions[0].value: This value should not be blank."
#
#  @javascript
#  Scenario: Fails when import rule with not existing to_field for copy action
#    Given the following yaml file to import:
#    """
#    rules:
#        canon_beautiful_description:
#            conditions:
#                - field:    name
#                  operator: CONTAINS
#                  value:    Canon
#            actions:
#                - type:       copy_value
#                  from_field: description
#                  to_field:   wrong
#
#    """
#    And the following job "clothing_rule_import" configuration:
#      | filePath | %file to import% |
#    When I am on the "clothing_rule_import" import job page
#    And I launch the import job
#    And I wait for the "clothing_rule_import" job to finish
#    And I should see "actions[0].value: This value should not be blank."
