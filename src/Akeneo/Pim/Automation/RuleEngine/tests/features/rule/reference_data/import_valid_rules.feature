Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

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
            enabled: false
            conditions:
                - field:    sleeve_color.code
                  operator: IN
                  value:
                    - red
                    - grizzly
                - field:    sleeve_fabric.code
                  operator: IN
                  value:
                    - kevlar
                    - chiffon
            actions:
                - type:  set
                  field: sleeve_color
                  value: yellow
                - type:  set
                  field: sleeve_fabric
                  value:
                    - kevlar
                    - chiffon
                    - satin
                    - wool
                - type:        copy
                  from_field:  zip_color
                  to_field:    zip_color
                  from_scope:  mobile
                  to_scope:    tablet
                  from_locale: en_US
                  to_locale:   en_US
    """
    Then no exception has been thrown
    And the rule list contains the rule:
    """
    set_reference_data:
        priority: 0
        enabled: false
        conditions:
            - field:    sleeve_color.code
              operator: IN
              value:
                - red
                - grizzly
            - field:    sleeve_fabric.code
              operator: IN
              value:
                - kevlar
                - chiffon
        actions:
            - type:  set
              field: sleeve_color
              value: yellow
            - type:  set
              field: sleeve_fabric
              value:
                - kevlar
                - chiffon
                - satin
                - wool
            - type:        copy
              from_field:  zip_color
              to_field:    zip_color
              from_scope:  mobile
              to_scope:    tablet
              from_locale: en_US
              to_locale:   en_US
    """
