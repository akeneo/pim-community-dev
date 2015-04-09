@javascript
Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And the following "sleeve_color" attribute reference data: yellow, blue
    And the following "sleeve_fabric" attribute reference data: chiffon, satin
    And I am logged in as "Peter"

  Scenario: Successfully import a rule for "reference data" attributes
    Given the following yaml file to import:
    """
    rules:
        set_reference_data:
            conditions:
                - field:    sleeve_color.code
                  operator: IN
                  value:
                    - red
                    - orange
                - field:    sleeve_fabric.code
                  operator: IN
                  value:
                    - kevlar
                    - chiffon
            actions:
                - type:  set_value
                  field: sleeve_color
                  value: green
                - type:  set_value
                  field: sleeve_fabric
                  value:
                    - kevlar
                    - chiffon
                    - satin
                    - wool
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 1"
    And I should see "Attribute or field \"sleeve_color\" expects a valid code. No reference data \"color\" with code \"red\" has been found, \"red,orange\" given (for setter reference data)"
    And I should see "Attribute or field \"sleeve_fabric\" expects a valid code. No reference data \"fabrics\" with code \"kevlar\" has been found, \"kevlar,chiffon\" given (for setter reference data)"
    And I should see "Attribute or field \"sleeve_color\" expects a valid code. No reference data \"color\" with code \"green\" has been found, \"green\" given (for setter reference data)"
    And I should see "Attribute or field \"sleeve_fabric\" expects an array with valid data for the key \"code\". No reference data \"fabrics\" with code \"kevlar\" has been found, \"kevlar\" given (for setter reference data collection)"
