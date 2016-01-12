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
    Then the row "set_reference_data" should contain the texts:
      | column    | value                                                                     |
      | Condition | If sleeve_color.code in red, orange                                       |
      | Condition | If sleeve_fabric.code in kevlar, chiffon                                  |
      | Action    | Then yellow is set into sleeve_color                                      |
      | Action    | Then kevlar, chiffon, satin, wool is set into sleeve_fabric               |
      | Action    | Then zip_color [ en \| mobile ] is copied into zip_color [ en \| tablet ] |
