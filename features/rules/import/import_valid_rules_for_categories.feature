@javascript
Feature: Import rules for categories
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import valid rule for "categories" attribute and "remove value" actions
    Given the following yaml file to import:
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
                  apply_children: true
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "created 2"
