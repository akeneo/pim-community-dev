@javascript
Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Skip rules with unsupported integer value for attribute name in conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  name
            value:  Super Name
            locale: en_US
      """
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
    And I should see "conditions[0]: Attribute or field \"name\" expects a string as data, \"integer\" given (for filter string)."
    And I should see "actions[0]: Attribute or field \"name\" expects a string as data, \"integer\" given."
    When I am on the "name" attribute page
    And I visit the "Rules" tab
    Then I should see "Super Name"

  Scenario: Skip rules with unsupported integer value for attribute of type textarea in conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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
    And I should see "conditions[0]: Attribute or field \"description\" expects a string as data, \"integer\" given (for filter string)."
    And I should see "actions[0]: Attribute or field \"description\" expects a string as data, \"integer\" given."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "Another good description"

  Scenario: Skip rules with unsupported integer value for attribute of type identifier in conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    sku
            operator: CONTAINS
            value:    "42"
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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
    And I should see "conditions[0]: Attribute or field \"SKU\" expects a string as data, \"integer\" given (for filter string)."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see "Another good description"

  Scenario: Skip rules with unsupported string value for attribute of type simple select in conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  manufacturer
            value:  Volcom
      """
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
                  value: Desigual
        sony_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value: not an array
            actions:
                - type:  set_value
                  field: manufacturer
                  value: Desigual
    """
    And the following job "clothing_rule_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_rule_import" import job page
    And I launch the import job
    And I wait for the "clothing_rule_import" job to finish
    Then I should see "skipped 2"
    And I should see "conditions[0]: Attribute or field \"manufacturer\" expects an array as data, \"string\" given (for filter option)."
    When I am on the "manufacturer" attribute page
    And I visit the "Rules" tab
    Then I should see "Volcom"

  Scenario: Skip rules with unsupported string value for the multi select attribute of type multi select in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  weather_conditions
            value:
              - dry
              - wet
      """
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
    And I should see "conditions[0]: Attribute or field \"weather_conditions\" expects an array as data, \"string\" given (for filter options)."
    And I should see "actions[0]: Attribute or field \"weather_conditions\" expects an array as data, \"string\" given (for setter multi select)."
    When I am on the "weather_conditions" attribute page
    And I visit the "Rules" tab
    Then I should see "dry"
    And I should see "wet"

  Scenario: Skip rules with unsupported array values for attribute of type multi select in conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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

  Scenario: Skip rules with unsupported values for attribute of type prices collection in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  price
            value:
              - data: 3
                currency: EUR
      """
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
    And I should see "conditions[0]: Attribute or field \"price\" expects an array as data, \"string\" given (for filter price)."
    And I should see "actions[0]: Attribute or field \"price\" expects an array as data, \"string\" given (for setter prices collection)."
    When I am on the "price" attribute page
    And I visit the "Rules" tab
    Then I should see "3"
    Then I should see "EUR"

  Scenario: Skip rules with unsupported values for attribute of type metric in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  length
            value:
              data: 3
              unit: CENTIMETER
      """
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
    And I should see "conditions[0]: Attribute or field \"length\" expects an array as data, \"string\" given (for filter metric)."
    And I should see "actions[0]: Attribute or field \"length\" expects an array as data, \"string\" given (for setter metric)."
    When I am on the "length" attribute page
    And I visit the "Rules" tab
    Then I should see "3"
    Then I should see "CENTIMETER"

  Scenario: Skip rules with unsupported values for attribute of type number in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  number_in_stock
            value:  42
            scope:  tablet
      """
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
    And I should see "conditions[0]: Attribute or field \"number_in_stock\" expects a numeric as data (for string filter)."
    And I should see "actions[0]: this value should be a valid number."
    When I am on the "number_in_stock" attribute page
    And I visit the "Rules" tab
    Then I should see "42"

  Scenario: Skip rules with unsupported values for attribute of type boolean in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  handmade
            value:  true
      """
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
    And I should see "conditions[0]: Attribute or field \"handmade\" expects a boolean as data, \"string\" given (for filter boolean)."
    And I should see "actions[0]: Attribute or field \"handmade\" expects a boolean as data, \"string\" given."
    When I am on the "handmade" attribute page
    And I visit the "Rules" tab
    Then I should see "true"

  Scenario: Skip rules with unsupported values for attribute of type date in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  release_date
            value:  "1970-01-01"
            scope:  tablet
      """
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
    And I should see "conditions[0]: Attribute or field \"release_date\" expects a string with the format yyyy-mm-dd as data, \"invalid\" given (for filter date)."
    And I should see "actions[0]: Attribute or field \"release_date\" expects a string with the format yyyy-mm-dd as data, \"string\" given (for setter date)."
    When I am on the "release_date" attribute page
    And I visit the "Rules" tab
    Then I should see "1/1/70"

  Scenario: Skip rules with unsupported values for attribute of type media in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  side_view
            value:
              originalFilename: SNKRS-1R.png
              filePath: %fixtures%/SNKRS-1R.png
      """
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
    And I should see "conditions[0]: Attribute or field \"side_view\" expects a string as data, \"array\" given (for filter media).: PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition"
    And I should see "actions[0]: Attribute or field \"side_view\" expects a valid pathname as data, \"invalid/path/to/image\" given (for setter media).: PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction"
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "SNKRS-1R"

  Scenario: Skip rules with missing values for attribute of type media in conditions and actions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set_value
            field:  side_view
            value:
              originalFilename: SNKRS-1R.png
              filePath: %fixtures%/SNKRS-1R.png
      """
    And the following yaml file to import:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
            actions:
                - type:  set_value
                  field: side_view
                  value:
                       originalFilename: image_name
        sony_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
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
    And I should see "conditions[0]: The key \"value\" is missing or empty."
    And I should see "conditions[0]: Attribute or field \"side_view\" expects a string as data"
    And I should see "actions[0]: Attribute or field \"side_view\" expects an array with the key \"filePath\" as data"
    When I am on the "side_view" attribute page
    And I visit the "Rules" tab
    Then I should see "SNKRS-1R"

  Scenario: Skip rules with missing conditions key
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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

  Scenario: Skip rules with missing operator key for conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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

  Scenario: Skip rules with missing value key for conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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

  Scenario: Skip rules with invalid operator for conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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

  Scenario: Skip rules with non existing field for conditions
    Given the following product rule definitions:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
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
    And I should see "conditions[0].field: The field \"wrong\" cannot be filtered."
    And I should see "conditions[0].field: The field \"another wrong\" cannot be filtered."
    When I am on the "description" attribute page
    And I visit the "Rules" tab
    And I should see "Another good description"
