Feature: Import rules for categories
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Import valid rule for "categories" attribute and "remove value" actions
    When the following yaml file is imported:
    """
    rules:
        remove_clothes_categories:
            conditions:
                -
                  field:    handmade
                  operator: =
                  value:    true
            actions:
                -
                  type:  remove
                  field: categories
                  items:
                      - summer_collection
                      - winter_collection
        remove_clothes_categories_and_children:
            conditions:
                -
                  field:    handmade
                  operator: =
                  value:    true
            actions:
                -
                  type:  remove
                  field: categories
                  items:
                      - 2014_collection
                  include_children: true
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

  @integration-back
  Scenario: Import valid rules with empty categories for UNCLASSIFIED operator
    When the following yaml file is imported:
    """
    rules:
      rule_with_valid_empty_value_for_categories:
        conditions:
          - field: categories
            operator: UNCLASSIFIED
            value: []
          - field: categories
            operator: UNCLASSIFIED
            value: ~
          - field: categories
            operator: UNCLASSIFIED
        actions:
          - type:  set
            field: name
            value: Test
            locale: en_US
    """
    Then no exception has been thrown
    And the rule list contains the imported rules

