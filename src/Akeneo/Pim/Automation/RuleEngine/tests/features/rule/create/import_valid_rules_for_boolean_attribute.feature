Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid rule for "boolean" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_boolean:
            conditions:
                - field:    handmade
                  operator: =
                  value:    true
            actions:
                - type:  set
                  field: handmade
                  value: true
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type boolean in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: handmade
                  to_field:   handmade
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
