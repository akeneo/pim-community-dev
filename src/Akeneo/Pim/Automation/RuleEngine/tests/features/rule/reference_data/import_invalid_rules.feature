Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I add the "fr_FR" locale to the "mobile" channel
    And I add the "fr_FR" locale to the "tablet" channel

  @integration-back
  Scenario: Successfully import a rule for "reference data" attributes
    When the following yaml file is imported:
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
    Then an exception with message "Property \"sleeve_color\" expects a valid code. No reference data \"color\" with code \"orange\" has been found, \"red,orange\" given" has been thrown
    And an exception with message "Property \"sleeve_fabric\" expects a valid code. No reference data \"fabrics\" with code \"amazing_kevlar\" has been found, \"amazing_kevlar,chiffon\" given" has been thrown
    And an exception with message "Property \"sleeve_color\" expects a valid reference data code. The code \"mr_green\" of the reference data \"color\" does not exist" has been thrown
    And an exception with message "Property \"sleeve_fabric\" expects valid codes. The following codes for reference data \"fabrics\" do not exist: \"amazing_kevlar, amazing_wool\"" has been thrown
    And the rule list does not contain the "set_reference_data" rule
