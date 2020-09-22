Feature: Export rules
  In order to be able to access and modify rules outside PIM
  As an administrator
  I need to be able to export rules

  @integration-back
  Scenario: Successfully export rules with reference data
    Given a "clothing" catalog configuration
    And I add the "fr_FR" locale to the "mobile" channel
    And I add the "fr_FR" locale to the "tablet" channel
    And the following product rule definitions:
      """
      set_reference_data:
        priority: 10
        enabled: true
        conditions:
          - field:    sleeve_color.code
            operator: IN
            value:
              - red
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
    And I export all the rules
    Then the exported yaml file should contain:
    """
    rules:
        set_reference_data:
            priority: 10
            enabled: true
            conditions:
                -
                    field: sleeve_color.code
                    operator: IN
                    value:
                        - red
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
            labels: {}
    """
