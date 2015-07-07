@javascript
Feature: Export rules
  In order to be able to access and modify rules outside PIM
  As an administrator
  I need to be able to export rules

  Scenario: Successfully export rules with reference data
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And the following "sleeve_color" attribute reference data: yellow, blue, red, orange
    And the following "sleeve_fabric" attribute reference data: chiffon, satin, wool, kevlar, leather, gore-tex, toile, cashmere
    And the following product rules:
      | code               | priority |
      | set_reference_data | 10       |
    And the following product rule conditions:
      | rule               | field              | operator | value           |
      | set_reference_data | sleeve_color.code  | IN       | red, orange     |
      | set_reference_data | sleeve_fabric.code | IN       | kevlar, chiffon |
    And the following product rule setter actions:
      | rule               | field         | value                        | locale | scope |
      | set_reference_data | sleeve_color  | yellow                       |        |       |
      | set_reference_data | sleeve_fabric | kevlar, chiffon, satin, wool |        |       |
    And the following product rule copier actions:
      | rule               | from_field | to_field  | from_locale | to_locale | from_scope | to_scope |
      | set_reference_data | zip_color  | zip_color | en_US       | en_US     | mobile     | tablet   |
    And the following job "clothing_rule_export" configuration:
      | filePath | %tmp%/rule_export/rule_export.yml |
    And I am logged in as "Peter"
    And I am on the "clothing_rule_export" export job page
    When I launch the export job
    And I wait for the "clothing_rule_export" job to finish
    Then exported file of "clothing_rule_export" should contain:
    """
    rules:
        set_reference_data:
            priority: 10
            conditions:
                -
                    field: sleeve_color.code
                    operator: IN
                    value:
                        - red
                        - orange
                -
                    field: sleeve_fabric.code
                    operator: IN
                    value:
                        - kevlar
                        - chiffon
            actions:
                -
                    type: set_value
                    field: sleeve_color
                    value: yellow
                -
                    type: set_value
                    field: sleeve_fabric
                    value:
                        - kevlar
                        - chiffon
                        - satin
                        - wool
                -
                    type: copy_value
                    from_field: zip_color
                    to_field: zip_color
                    from_scope: mobile
                    to_scope: tablet
                    from_locale: en_US
                    to_locale: en_US
    """
