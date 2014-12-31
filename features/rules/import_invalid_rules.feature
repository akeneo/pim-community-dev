@javascript
Feature: Import rules
  In order ease the enrichment of the catalog
  As an administrator
  I need to be able to import rules

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Skip rules with unsupported integer value for attribute name in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field | value      | locale |
      | sony_beautiful_description | name  | Super Name | en_US  |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set_value
                  field: name
                  value: 42
                  locale: en_US
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set_value
                  field: name
                  value: 42
                  locale: en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"name\" expects a string as data (for filter string)."
    And I should see "actions[0]: Attribute \"name\" expects a string as data, \"integer\" given (for setter text)."
    When I am on the "name" attribute page
    And I visit the "Rules" tab
    Then I should see "Super Name"

  Scenario: Skip rules with unsupported integer value for attribute of type textarea in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
                  scope:    tablet
            actions:
                - type:  set_value
                  field: description
                  value: 42
                  locale: en_US
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
                  scope:    tablet
            actions:
                - type:  set_value
                  field: description
                  value: 42
                  locale: en_US
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"description\" expects a string as data (for filter string)."
    And I should see "actions[0]: Attribute \"description\" expects a string as data, \"integer\" given (for setter text)."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "Another good description"

  Scenario: Skip rules with unsupported integer value for attribute of type identifier in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value |
      | sony_beautiful_description | SKU   | CONTAINS | 42    |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    SKU
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set_value
                  field: SKU
                  value: 42
                  locale: en_US
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set_value
                  field: SKU
                  value: 42
                  locale: en_US
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"SKU\" expects a string as data (for filter string)."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "Another good description"

  Scenario: Skip rules with unsupported string value for attribute of type simple select in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field        | value  |
      | sony_beautiful_description | manufacturer | Volcom |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value:    not an array
            actions:
                - type:  set_value
                  field: manufacturer
                  value: not an array
        sony_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value: not an array
            actions:
                - type:  set_value
                  field: manufacturer
                  value: not an array
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"manufacturer\" expects an array as data (for filter option)."
    And I should see "actions[0]: Attribute \"manufacturer\" expects an array as data, \"string\" given (for setter simple select)."
    When I am on the "manufacturer" attribute page
    And I visit the "Rules" tab
    Then I should see "Volcom"

  Scenario: Skip rules with unsupported string value for the multi select attribute of type multi select in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field              | value   |
      | sony_beautiful_description | weather_conditions | dry,wet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:    not an array
            actions:
                - type:  set_value
                  field: weather_conditions
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:    not an array
            actions:
                - type:   set_value
                  field:  weather_conditions
                  value:  The new Sony description
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"weather_conditions\" expects an array as data (for filter options)."
    And I should see "actions[0]: Attribute \"weather_conditions\" expects an array as data, \"string\" given (for setter multi select)."
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then I should see "dry"
    And I should see "wet"

  Scenario: Skip rules with unsupported array values for attribute of type multi select in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
                  locale: en_US
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description
                  locale: en_US
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Object \"option\" with code \"invalid\" does not exist"
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "Another good description"

  Scenario: Skip rules with unsupported values for attribute of type prices collection in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field | value |
      | sony_beautiful_description | price | 3,EUR |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    price
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: price
                  value: Invalid data for price
        sony_beautiful_description:
            conditions:
                - field:    price
                  operator: =
                  value: invalid
            actions:
                - type:  set_value
                  field: price
                  value: Invalid data for price
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Only scalar values are allowed for operators eq, lt, lte, gt, gte, like."
    And I should see "actions[0]: Attribute \"price\" expects an array as data, \"string\" given (for setter prices collection)."
    When I am on the "price" attribute page
    And I visit the "Rules" tab
    Then I should see "3"
    Then I should see "EUR"

  Scenario: Skip rules with unsupported values for attribute of type metric in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field  | value        |
      | sony_beautiful_description | length | 3,CENTIMETER |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    length
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: length
                  value: Invalid data
        sony_beautiful_description:
            conditions:
                - field:    length
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: length
                  value: Invalid data
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"length\" expects a numeric as data (for filter metric)."
    And I should see "actions[0]: Attribute \"length\" expects an array as data, \"string\" given (for setter metric)."
    When I am on the "length" attribute page
    And I visit the "Rules" tab
    Then I should see "3"
    Then I should see "CENTIMETER"

  Scenario: Skip rules with unsupported values for attribute of type number in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field           | value | scope  |
      | sony_beautiful_description | number_in_stock | 42    | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    invalid
                  scope:    tablet
            actions:
                - type:  set_value
                  field: number_in_stock
                  value: invalid
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: number_in_stock
                  value: invalid
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"number_in_stock\" expects a numeric as data (for filter number)."
    And I should see "actions[0]: Attribute \"number_in_stock\" expects a numeric as data, \"string\" given (for setter number)."
    When I am on the "number_in_stock" attribute page
    And I visit the "Rules" tab
    Then I should see "42"

  Scenario: Skip rules with unsupported values for attribute of type boolean in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field    | value |
      | sony_beautiful_description | handmade | true  |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    handmade
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: handmade
                  value: invalid
        sony_beautiful_description:
            conditions:
                - field:    handmade
                  operator: =
                  value:    invalid
            actions:
                - type:  set_value
                  field: handmade
                  value: invalid
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"handmade\" expects a boolean as data (for filter boolean)."
    And I should see "actions[0]: Attribute \"handmade\" expects a boolean as data, \"string\" given (for setter boolean)."
    When I am on the "handmade" attribute page
    And I visit the "Rules" tab
    Then I should see "true"

  Scenario: Skip rules with unsupported values for attribute of type date in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field        | value      | scope  |
      | sony_beautiful_description | release_date | 1970-01-01 | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    release_date
                  operator: =
                  value:    invalid
                  scope:    tablet
            actions:
                - type:  set_value
                  field: release_date
                  value: invalid
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    release_date
                  operator: =
                  value:    invalid
                  scope:   tablet
            actions:
                - type:  set_value
                  field: release_date
                  value: invalid
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"release_date\" expects a string with the format yyyy-mm-dd as data (for filter date)."
    And I should see "actions[0]: Attribute \"release_date\" expects a string with the format yyyy-mm-dd as data, \"string\" given (for setter date)."
    When I am on the "release_date" attribute page
    And I visit the "Rules" tab
    Then I should see "1970-01-01"

  Scenario: Skip rules with unsupported values for attribute of type media in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field     | value                                                    |
      | sony_beautiful_description | side_view | SNKRS-1R,../../../features/Context/fixtures/SNKRS-1R.png |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       filePath:         invalid/path/to/image
                       originalFilename: image_name
        sony_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       filePath:         invalid/path/to/image
                       originalFilename: image_name
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Only scalar values are allowed for operators eq, lt, lte, gt, gte, like."
    And I should see "actions[0]: Attribute \"side_view\" expects a valid file path"
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "../../../features/Context/fixtures/SNKRS-1R.png"
    Then I should see "SNKRS-1R"

  Scenario: Skip rules with missing values for attribute of type media in conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | fr_FR  |
    And the following product rule setter actions:
      | rule                       | field     | value                                                    |
      | sony_beautiful_description | side_view | SNKRS-1R,../../../features/Context/fixtures/SNKRS-1R.png |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       originalFilename: image_name
        sony_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       originalFilename: image_name
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Only scalar values are allowed for operators eq, lt, lte, gt, gte, like."
    And I should see "actions[0]: Attribute \"side_view\" expects an array with the key"
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "Context/fixtures/SNKRS-1R.png"
    Then I should see "SNKRS-1R"

  Scenario: Skip rules with missing conditions key
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            wrong:
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
        sony_beautiful_description:
            wrong:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description
                  locale: en_US
                  scope: tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule content \"canon_beautiful_description\" should have a \"conditions\" key."
    And I should see "Rule content \"sony_beautiful_description\" should have a \"conditions\" key."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with missing actions key
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            wrong:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            wrong:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule content \"canon_beautiful_description\" should have a \"actions\" key."
    And I should see "Rule content \"sony_beautiful_description\" should have a \"actions\" key."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "sony_beautiful_description"
    And I should see "Another good description"

  Scenario: Skip rules with missing operator key for conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:          name
                  wrong_operator: CONTAINS
                  value:          Canon
                  locale:         en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:       name
                  wrong_operator: CONTAINS
                  value:       Canon
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0].operator: The key \"operator\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "sony_beautiful_description"
    And I should see "Another good description"

  Scenario: Skip rules with missing field key for conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - wrong_field: name
                  operator:    CONTAINS
                  value:       Canon
                  locale:      en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - wrong_field: name
                  operator:    CONTAINS
                  value:       Canon
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0].field: The key \"field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "sony_beautiful_description"
    And I should see "Another good description"

  Scenario: Skip rules with missing from_field key for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:     copy_value
                  to_field: description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:     copy_value
                  to_field: name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].fromField: The key \"from_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with missing to_field key for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: camera_model_name
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:       copy_value
                  from_field: camera_model_name

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].toField: The key \"to_field\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with missing value key for conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  locale:   en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: The key \"value\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with missing value key for set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
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
                  wrong: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  wrong: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].value: The key \"value\" is missing or empty."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with invalid operator for conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: WRONG
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: ANOTHER WRONG
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: The operator \"WRONG\" is not supported by the field \"name\""
    And I should see "conditions[0]: The operator \"ANOTHER WRONG\" is not supported by the field \"name\""
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules without locale key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set_value
                  field:  description
                  value:  A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"name\" excepts valid data, scope and locale (for filter string). Attribute \"name\" expects a locale, none given."
    And I should see "actions[0]: Attribute \"description\" expects valid data, scope and locale (for setter text). Attribute \"description\" expects a locale, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with nonexistent locale key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   kj_KL
                  scope:    tablet
            actions:
                - type:   set_value
                  field:  description
                  value:  A beautiful description
                  locale: kj_KL
                  scope:  tablet
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   kj_KL
                  scope:    tablet
            actions:
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
                  locale: kj_KL
                  scope:  tablet
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"name\" excepts valid data, scope and locale (for filter string). Attribute \"name\" expects an existing and activated locale, \"kj_KL\" given."
    And I should see "actions[0]: Attribute \"description\" expects valid data, scope and locale (for setter text). Attribute \"description\" expects an existing and activated locale, \"kj_KL\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules without scope key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set_value
                  field:  description
                  value:  A beautiful description
                  locale: en_US
        sony_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
                  locale: en_US
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for filter string). Attribute \"description\" expects a scope, none given."
    And I should see "actions[0]: Attribute \"description\" expects valid data, scope and locale (for setter text). Attribute \"description\" expects a scope, none given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with nonexistent scope key for condition and set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
                  scope:    nonexistent
            actions:
                - type:   set_value
                  field:  description
                  value:  A beautiful description
                  locale: en_US
                  scope:  nonexistent
        sony_beautiful_description:
            conditions:
                - field:    description
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
                  scope:    nonexistent
            actions:
                - type:   set_value
                  field:  description
                  value:  Another beautiful description
                  locale: en_US
                  scope:  nonexistent
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    And I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"description\" excepts valid data, scope and locale (for filter string). Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    And I should see "actions[0]: Attribute \"description\" expects valid data, scope and locale (for setter text). Attribute \"description\" expects an existing scope, \"nonexistent\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with missing type key for copy or set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - wrong: set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - wrong: set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule content \"canon_beautiful_description\" has an action with no type."
    And I should see "Rule content \"sony_beautiful_description\" has an action with no type."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with invalid type for copy or set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  wrong
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  wrong
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "Rule \"canon_beautiful_description\" has an unknown type of action \"wrong\"."
    And I should see "Rule \"sony_beautiful_description\" has an unknown type of action \"wrong\"."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with non existing field for conditions
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    wrong
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set_value
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    another wrong
                  operator: CONTAINS
                  value:    Canon
            actions:
                - type:  set_value
                  field: description
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0].field: The field \"wrong\" does not exist."
    And I should see "conditions[0].field: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with non existing field for set action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule setter actions:
      | rule                       | field       | value                    | locale | scope  |
      | sony_beautiful_description | description | Another good description | en_US  | tablet |
    And the following yaml file to import:
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
                  field: wrong
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set_value
                  field: another wrong
                  value: The new Sony description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].field: The field \"wrong\" does not exist."
    And I should see "actions[0].field: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"

  Scenario: Skip rules with non existing from_field for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: wrong
                  to_field:   description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: another wrong
                  to_field:   description

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].fromField: The field \"wrong\" does not exist."
    And I should see "actions[0].fromField: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with non existing to_field for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field        | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description     | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   another wrong

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0].toField: The field \"wrong\" does not exist."
    And I should see "actions[0].toField: The field \"another wrong\" does not exist."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with incompatible fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   side_view
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:       copy_value
                  from_field: description
                  to_field:   side_view

    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0]: Source and destination attributes \"description\" and \"side_view\" are not supported by any copier"
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with wrong locale fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy_value
                  from_field:  name
                  to_field:    name
                  from_locale: wrong
                  to_locale:   wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy_value
                  from_field:  name
                  to_field:    name
                  from_locale: wrong
                  to_locale:   wrong
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0]: Attribute \"name\" expects valid data, scope and locale (for copier base). Attribute \"name\" expects an existing and activated locale, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |

  Scenario: Skip rules with wrong scope fields for copy action
    Given the following product rules:
      | code                       | priority |
      | sony_beautiful_description | 10       |
    And the following product rule conditions:
      | rule                       | field | operator | value | locale |
      | sony_beautiful_description | name  | CONTAINS | Canon | en_US  |
    And the following product rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en_US       | en_US     | mobile     | tablet   |
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy_value
                  from_field:  description
                  to_field:    description
                  from_locale: en_US
                  to_locale:   en_US
                  from_scope:  wrong
                  to_scope:    wrong
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:        copy_value
                  from_field:  description
                  to_field:    description
                  from_locale: en_US
                  to_locale:   en_US
                  from_scope:  wrong
                  to_scope:    wrong
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "actions[0]: Attribute \"description\" expects valid data, scope and locale (for copier base). Attribute \"description\" expects an existing scope, \"wrong\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule copier actions:
      | rule                       | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | sony_beautiful_description | description | description | en          | en        | mobile     | tablet   |
