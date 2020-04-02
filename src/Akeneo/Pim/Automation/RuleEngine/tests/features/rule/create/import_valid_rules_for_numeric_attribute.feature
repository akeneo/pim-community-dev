Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with numeric attributes

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid rule for "price collection" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        sony_beautiful_price:
            conditions:
                - field: price
                  operator: =
                  value:
                      amount: 35
                      currency: EUR
            actions:
                - type:  set
                  field: price
                  value:
                       - amount: 3
                         currency: EUR
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "metric attribute" in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_length:
            conditions:
                - field:    length
                  operator: =
                  value:
                      amount: 156
                      unit: METER
            actions:
                - type:  set
                  field: length
                  value:
                      amount: 4
                      unit: CENTIMETER
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "number" attribute in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_number:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    5
                  scope: tablet
            actions:
                - type:  set
                  field: number_in_stock
                  value: 5
                  scope: tablet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rule for "date" attribute (with a string for a date) in conditions and "set value" actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_date:
            conditions:
                - field:    release_date
                  operator: =
                  value:    "1970-01-01"
                  scope: tablet
            actions:
                - type:  set
                  field: release_date
                  value: "1970-01-01"
                  scope: tablet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type date in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: release_date
                  to_field:   release_date
                  from_scope: mobile
                  to_scope:   tablet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type metric in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy
                  from_field:  length
                  to_field:    length
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type price in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: price
                  to_field:   price
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import a copy value rule with valid values for attribute of type number in actions
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: number_in_stock
                  to_field:   number_in_stock
                  from_scope: mobile
                  to_scope:   tablet
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
