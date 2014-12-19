Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully import a rule
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
                  locale: en_US
                  scope: tablet

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule                        | field | operator | value |
      | canon_beautiful_description | name  | CONTAINS | Canon |
    Then I should see the following rule setter actions:
      | rule                        | field       | value                   | locale | scope  |
      | canon_beautiful_description | description | A beautiful description | en     | tablet |

  @javascript
  Scenario: Import rule with valid value for attribute of type text in conditions
    Given the following yaml file to import:
    """
    rules:
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set_value
                  field: name
                  value: My new Super Name
                  locale: en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"sony_beautiful_description\" as it does not appear to be valid."
    When I am on the "name" attribute page
    And I visit the "Rules" tab
    Then I should see "My new Super Name"

  @javascript
  Scenario: Import rule with valid values for attribute of type textarea in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Another good description
                  locale:   fr_FR
                  scope:    tablet
            actions:
                - type:   set_value
                  field:  description
                  value:  My new description
                  locale: en_US
                  scope:  tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    And I should see "created 1"
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "My new description"

  @javascript
  Scenario: Import rule with valid values for attribute of type simple select in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value:
                      - Volcom
            actions:
                - type:  set_value
                  field: manufacturer
                  value:
                      code:      Desigual
                      attribute: manufacturer
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "created 1"
    Then I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "manufacturer" attribute page
    And I visit the "Rules" tab
    Then I should see "Desigual"

  @javascript
  Scenario: Import rule with valid values for the multi select attribute weather_conditions in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    weather_conditions
                  operator: IN
                  value:
                      - dry
            actions:
                - type:  set_value
                  field: weather_conditions
                  value:
                      - code: dry
                        attribute: weather_conditions
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "created 1"
    And I should not see "skipped"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then I should see "dry"
    And I should not see "wet"

  @javascript
  Scenario: Import rule with valid values for attribute of type price collection in conditions
    Given the following yaml file to import:
    """
    rules:
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Super Name
                  locale:   fr_FR
            actions:
                - type:  set_value
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
    Then I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"sony_beautiful_description\" as it does not appear to be valid."
    When I am on the "price" attribute page
    And I visit the "Rules" tab
    Then I should see "3"
    Then I should see "EUR"

  @javascript
  Scenario: Import rule with valid values for attribute of type metric in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
            actions:
                - type:  set_value
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
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "length" attribute page
    And I visit the "Rules" tab
    Then I should see "4"
    Then I should see "CENTIMETER"

  @javascript
  Scenario: Import rule with valid values for attribute of type number in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    5
                  scope: tablet
            actions:
                - type:  set_value
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
    Then I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "number_in_stock" attribute page
    And I visit the "Rules" tab
    Then I should see "5"

  @javascript
  Scenario: Import rule with valid values for attribute of type boolean in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    handmade
                  operator: =
                  value:    true
            actions:
                - type:  set_value
                  field: handmade
                  value: true
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    Then I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "handmade" attribute page
    And I visit the "Rules" tab
    Then I should see "true"

  @javascript
  Scenario: Import rule with valid values for attribute of type date in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    release_date
                  operator: =
                  value:    "1970-01-01"
                  scope: tablet
            actions:
                - type:  set_value
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
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "release_date" attribute page
    And I visit the "Rules" tab
    Then I should see "1970-01-01"

  @javascript
  Scenario: Import rule with valid values for attribute of type media in conditions
    Given the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       filePath:         ../../../features/Context/fixtures/akeneo.jpg
                       originalFilename: akeneo
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should not see "skipped"
    And I should see "created 1"
    And I should not see "RULE IMPORT  Impossible to build the rule \"canon_beautiful_description\" as it does not appear to be valid."
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "Context/fixtures/akeneo.jpg"
    Then I should see "akeneo.jpg"
