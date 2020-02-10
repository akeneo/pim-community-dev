@javascript
Feature: Export rules
  In order to be able to access and modify rules outside PIM
  As an administrator
  I need to be able to export rules

  Scenario: Successfully export rules with reference data
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And the following "sleeve_color" attribute reference data: orange
    And the following product rule definitions:
      """
      set_reference_data:
        priority: 10
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
          - type:   set
            field:  sleeve_color
            value:  yellow
          - type:   set
            field:  sleeve_fabric
            value:
              - kevlar
              - chiffon
              - satin
              - wool
          - type:   copy
            from_field: zip_color
            to_field: zip_color
            from_locale: en_US
            to_locale: en_US
            from_scope: mobile
            to_scope: tablet
      """
    And the following job "clothing_rule_export" configuration:
      | filePath | %tmp%/rule_export/rule_export.yml |
    And I am logged in as "Peter"
    And I am on the "clothing_rule_export" export job page
    When I launch the export job
    And I wait for the "clothing_rule_export" job to finish
    Then exported yaml file of "clothing_rule_export" should contain:
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
                    field: sleeve_color
                    type: set
                    value: yellow
                -
                    field: sleeve_fabric
                    type: set
                    value:
                        - kevlar
                        - chiffon
                        - satin
                        - wool
                -
                    from_field: zip_color
                    from_locale: en_US
                    from_scope: mobile
                    to_field: zip_color
                    to_locale: en_US
                    to_scope: tablet
                    type: copy
    """
