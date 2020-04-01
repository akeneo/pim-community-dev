Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules with non standard values

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import a rule with valid but non standard values
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_media:
            conditions:
                - field:    sku
                  operator: CONTAINS
                  value:    '0'
                - field:    description
                  operator: CONTAINS
                  value:    '0'
                  locale:   en_US
                  scope:    tablet
                - field:    handmade
                  operator: =
                  value:    false
                - field:    number_in_stock
                  operator: =
                  value:    0
                  scope:    tablet
                - field:    length
                  operator: =
                  value:
                      amount: 0
                      unit: CENTIMETER
                - field:    price
                  operator: =
                  value:
                      amount:     0
                      currency: EUR
            actions:
                - type:        copy
                  from_field:  side_view
                  to_field:    side_view
    """
    Then no exception has been thrown
    And the rule list contains the imported rules
