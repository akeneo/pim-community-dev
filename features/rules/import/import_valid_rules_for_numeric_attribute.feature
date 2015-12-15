@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Import valid rule for "price collection" attribute in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        sony_beautiful_price:
            conditions:
                - field: price
                  operator: =
                  value:
                      data: 35
                      currency: EUR
            actions:
                - type:  set
                  field: price
                  value:
                       - data: 3
                         currency: EUR
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"sony_beautiful_price\" as it does not appear to be valid."
    When I am on the "price" attribute page
    And I visit the "Rules" tab
    Then I should see the text "€3"

  Scenario: Import valid rule for "metric attribute" in conditions and "set value" actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_length:
            conditions:
                - field:    length
                  operator: =
                  value:
                      data: 156
                      unit: METER
            actions:
                - type:  set
                  field: length
                  value:
                      data: 4
                      unit: CENTIMETER
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_length\" as it does not appear to be valid."
    When I am on the "length" attribute page
    And I visit the "Rules" tab
    Then I should see the text "4"
    Then I should see the text "CENTIMETER"

  Scenario: Import valid rule for "number" attribute in conditions and "set value" actions
    Given the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    Then I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_number\" as it does not appear to be valid."
    When I am on the "number_in_stock" attribute page
    And I visit the "Rules" tab
    Then I should see the text "5"

  Scenario: Import valid rule for "date" attribute (with a string for a date) in conditions and "set value" actions
    Given the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_date\" as it does not appear to be valid."
    When I am on the "release_date" attribute page
    And I visit the "Rules" tab
    Then I should see the text "1970-01-01"

  Scenario: Import a copy value rule with valid values for attribute of type date in actions
    Given the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "release_date" attribute page
    And I visit the "Rules" tab
    Then I should see the text "release_date"
    Then I should see the text "mobile"
    Then I should see the text "is copied into"
    Then I should see the text "tablet"

  Scenario: Import a copy value rule with valid values for attribute of type metric in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:        copy
                  from_field:  length
                  to_field:    length
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "length" attribute page
    And I visit the "Rules" tab
    Then I should see the text "length"
    Then I should see the text "is copied into"

  Scenario: Import a copy value rule with valid values for attribute of type price in actions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions: []
            actions:
                - type:       copy
                  from_field: price
                  to_field:   price
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "price" attribute page
    And I visit the "Rules" tab
    Then I should see the text "price"
    Then I should see the text "is copied into"

  Scenario: Import a copy value rule with valid values for attribute of type number in actions
    Given the following yaml file to import:
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
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see the text "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "number_in_stock" attribute page
    And I visit the "Rules" tab
    Then I should see the text "number_in_stock"
    Then I should see the text "mobile"
    Then I should see the text "is copied into"
    Then I should see the text "tablet"
