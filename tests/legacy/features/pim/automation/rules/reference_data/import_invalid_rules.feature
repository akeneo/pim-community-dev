@javascript
Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
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
                    - amazing_kevlar
                    - chiffon
            actions:
                - type:  set
                  field: sleeve_color
                  value: mr_green
                - type:  set
                  field: sleeve_fabric
                  value:
                    - amazing_kevlar
                    - chiffon
                    - satin
                    - amazing_wool
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "Property \"sleeve_color\" expects a valid code. No reference data \"color\" with code \"orange\" has been found, \"red,orange\" given"
    And I should see the text "Property \"sleeve_fabric\" expects a valid code. No reference data \"fabrics\" with code \"amazing_kevlar\" has been found, \"amazing_kevlar,chiffon\" given"
    And I should see the text "Property \"sleeve_color\" expects a valid reference data code. The code \"mr_green\" of the reference data \"color\" does not exist"
    And I should see the text "Property \"sleeve_fabric\" expects valid codes. The following codes for reference data \"fabrics\" do not exist: \"amazing_kevlar, amazing_wool\""
