@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And the following "sleeve_color" attribute reference data: yellow, blue, red, orange
    And the following "sleeve_fabric" attribute reference data: chiffon, satin, wool, kevlar, leather, gore-tex, toile, cashmere
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
                  value: yellow
                - type:  set_value
                  field: sleeve_fabric
                  value:
                    - kevlar
                    - chiffon
                    - satin
                    - wool
                - type:        copy_value
                  from_field:  zip_color
                  to_field:    zip_color
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
    And I am on the "sleeve_color" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule               | field              | operator | value           |
      | set_reference_data | sleeve_color.code  | IN       | red, orange     |
      | set_reference_data | sleeve_fabric.code | IN       | kevlar, chiffon |
    Then I should see the following rule setter actions:
      | rule               | field         | value                        | locale | scope |
      | set_reference_data | sleeve_color  | yellow                       |        |       |
      | set_reference_data | sleeve_fabric | kevlar, chiffon, satin, wool |        |       |
    Then I should see the following rule copier actions:
      | rule               | from_field | to_field  | from_locale | to_locale | from_scope | to_scope |
      | set_reference_data | zip_color  | zip_color | en          | en        | mobile     | tablet   |
