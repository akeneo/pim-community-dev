@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import a rule with valid but non standard values
    Given the following yaml file to import:
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
                      data: 0
                      unit: CENTIMETER
                - field:    price
                  operator: =
                  value:
                      data:     0
                      currency: EUR
            actions:
                - type:        copy_value
                  from_field:  side_view
                  to_field:    side_view
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "side_view"
    Then I should see "is copied into"
