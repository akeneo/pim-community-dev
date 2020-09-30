Feature: Import rules with status
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with enabled/disabled status

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid enabled and disabled rules
    When the following yaml file is imported:
    """
    rules:
        enabled_rule:
            enabled: true
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set
                  field: name
                  value: My new Super Name
                  locale: en_US
        disabled_rule:
            enabled: false
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set
                  field: name
                  value: My new Super Name
                  locale: en_US
    """
    Then no exception has been thrown
    And the enabled_rule rule is enabled
    And the disabled_rule rule is disabled
