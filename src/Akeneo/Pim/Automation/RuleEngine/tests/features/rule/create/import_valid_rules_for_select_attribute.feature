Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with select attribute

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid rule for "simple select" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_manufacturer:
            conditions:
                - field:    manufacturer.code
                  operator: IN
                  value:
                      - Volcom
            actions:
                - type:  set
                  field: manufacturer
                  value: Desigual
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "multi select" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_weather:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - dry
            actions:
                - type:  set
                  field: weather_conditions
                  value:
                      - dry
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type multi select in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: weather_conditions
                  to_field:   weather_conditions
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type simple select in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: manufacturer
                  to_field:   manufacturer
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "multi select" attribute in conditions and "remove value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_weather:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - dry
            actions:
                - type:  remove
                  field: weather_conditions
                  items:
                      - dry
                      - wet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
