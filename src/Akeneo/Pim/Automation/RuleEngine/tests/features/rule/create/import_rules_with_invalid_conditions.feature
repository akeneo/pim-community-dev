Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration

  @integration-back
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
          - type:   set
            field:  name
            value:  Super Name
            locale: en_US
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set
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
                - type:  set
                  field: name
                  value: 42
                  locale: en_US
    """
    Then an exception with message "conditions[0]: Property \"name\" expects a string as data, \"integer\" given." has been thrown
    And an exception with message "actions[0]: The name attribute requires a string, a integer was detected." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  name
            value:  Super Name
            locale: en_US
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
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
                - type:  set
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
                - type:  set
                  field: description
                  value: 42
                  locale: en_US
                  scope: tablet
    """
    Then an exception with message "conditions[0]: Property \"description\" expects a string as data, \"integer\" given." has been thrown
    And an exception with message "actions[0]: The description attribute requires a string, a integer was detected." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    sku
                  operator: CONTAINS
                  value:    42
                  locale:   en_US
            actions:
                - type:  set
                  field: sku
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
                - type:  set
                  field: sku
                  value: 42
                  locale: en_US
                  scope: tablet
    """
    Then an exception with message "conditions[0]: Property \"sku\" expects a string as data, \"integer\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    sku
            operator: CONTAINS
            value:    "42"
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  manufacturer
            value:  Volcom
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value:    not an array
            actions:
                - type:  set
                  field: manufacturer
                  value: Desigual
        sony_beautiful_description:
            conditions:
                - field:    manufacturer
                  operator: IN
                  value: not an array
            actions:
                - type:  set
                  field: manufacturer
                  value: Desigual
    """
    Then an exception with message "conditions[0]: Property \"manufacturer\" expects an array as data, \"string\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  manufacturer
            value:  Volcom
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  weather_conditions
            value:
              - dry
              - wet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:    not an array
            actions:
                - type:  set
                  field: weather_conditions
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:    not an array
            actions:
                - type:   set
                  field:  weather_conditions
                  value:  The new Sony description
        remove_sony_weather_conditions:
            conditions:
                -
                  field:    weather_conditions.code
                  operator: IN
                  value:    not an array
            actions:
                -
                  type:   remove
                  field:  weather_conditions
                  items:  The new Sony description
    """
    Then an exception with message "conditions[0]: Property \"weather_conditions\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"weather_conditions\" expects an array as data, \"string\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  weather_conditions
            value:
              - dry
              - wet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    weather_conditions.code
                  operator: IN
                  value:
                      - invalid
            actions:
                - type:  set
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
                - type:  set
                  field: description
                  value: The new Sony description
                  locale: en_US
                  scope: tablet
    """
    Then an exception with message "conditions[0]: Object \"options\" with code \"invalid\" does not exist" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  price
            value:
              - amount: 3
                currency: EUR
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    price
                  operator: =
                  value:    invalid
            actions:
                - type:  set
                  field: price
                  value: Invalid data for price
        sony_beautiful_description:
            conditions:
                - field:    price
                  operator: =
                  value: invalid
            actions:
                - type:  set
                  field: price
                  value: Invalid data for price
    """
    Then an exception with message "conditions[0]: Property \"price\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"price\" expects an array as data, \"string\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  price
            value:
              - amount: 3
                currency: EUR
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  length
            value:
              amount: 3
              unit: CENTIMETER
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    length
                  operator: =
                  value:    invalid
            actions:
                - type:  set
                  field: length
                  value: Invalid data
        sony_beautiful_description:
            conditions:
                - field:    length
                  operator: =
                  value:    invalid
            actions:
                - type:  set
                  field: length
                  value: Invalid data
    """
    Then an exception with message "conditions[0]: Property \"length\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"length\" expects an array as data, \"string\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  length
            value:
              amount: 3
              unit: CENTIMETER
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  number_in_stock
            value:  42
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    invalid
                  scope:    tablet
            actions:
                - type: set
                  field: number_in_stock
                  value: invalid
                  scope: tablet
        sony_beautiful_description:
            conditions:
                - field:    number_in_stock
                  operator: =
                  value:    invalid
            actions:
                - type: set
                  field: number_in_stock
                  value: invalid
                  scope: tablet
    """
    Then an exception with message "conditions[0]: Property \"number_in_stock\" expects a numeric as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: The number_in_stock attribute requires a number, and the submitted invalid value is not." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  number_in_stock
            value:  42
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  handmade
            value:  true
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    handmade
                  operator: =
                  value:    invalid
            actions:
                - type:  set
                  field: handmade
                  value: invalid
        sony_beautiful_description:
            conditions:
                - field:    handmade
                  operator: =
                  value:    invalid
            actions:
                - type:  set
                  field: handmade
                  value: invalid
    """
    Then an exception with message "conditions[0]: Property \"handmade\" expects a boolean as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"handmade\" expects a boolean as data, \"string\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  handmade
            value:  true
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  release_date
            value:  "1970-01-01"
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    release_date
                  operator: =
                  value:    invalid
                  scope:    tablet
            actions:
                - type:  set
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
                - type:  set
                  field: release_date
                  value: invalid
                  scope: tablet
    """
    Then an exception with message "conditions[0]: Property \"release_date\" expects a string with the format \"yyyy-mm-dd\" as data, \"invalid\" given." has been thrown
    And an exception with message "actions[0]: Property \"release_date\" expects a string with the format \"yyyy-mm-dd\" as data, \"invalid\" given." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   fr_FR
        actions:
          - type:   set
            field:  release_date
            value:  "1970-01-01"
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  side_view
            value:  %fixtures%/SNKRS-1R.png
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set
                  field: side_view
                  value: invalid/path/to/image
        sony_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
                  value:
                      - invalid
            actions:
                - type:  set
                  field: side_view
                  value: invalid/path/to/image
    """
    Then an exception with message "conditions[0]: Property \"side_view\" expects a string as data, \"array\" given." has been thrown
    And an exception with message "actions[0]: Property \"side_view\" expects a valid pathname as data, \"invalid/path/to/image\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with array values for attribute of type media in conditions and actions
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
          - type:   set
            field:  side_view
            value: %fixtures%/SNKRS-1R.png
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
            actions:
                - type:  set
                  field: side_view
                  value:
                      originalFilename: image_name
        sony_beautiful_description:
            conditions:
                - field:    side_view
                  operator: =
            actions:
                - type:  set
                  field: side_view
                  value:
                      originalFilename: image_name
    """
    Then an exception with message "conditions[0]: The \"value\" key is missing or empty" has been thrown
    And an exception with message "actions[0]: Property \"side_view\" expects a string as data, \"array\" given" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            wrong:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
                  locale: en_US
                  scope: tablet
        sony_beautiful_description:
            wrong:
                - field:    name
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description
                  locale: en_US
                  scope: tablet
    """
    Then an exception with message "Rule content \"canon_beautiful_description\" should have a \"conditions\" key." has been thrown
    And an exception with message "Rule content \"sony_beautiful_description\" should have a \"conditions\" key." has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:          name
                  wrong_operator: CONTAINS
                  value:          Canon
                  locale:         en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:          name
                  wrong_operator: CONTAINS
                  value:          Canon
                  locale:         en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "conditions[0].operator: The \"operator\" key is missing or empty" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - wrong_field: name
                  operator:    CONTAINS
                  value:       Canon
                  locale:      en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - wrong_field: name
                  operator:    CONTAINS
                  value:       Canon
                  locale:      en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "conditions[0].field: The \"field\" key is missing or empty" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: CONTAINS
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "conditions[0]: The \"value\" key is missing or empty" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    name
                  operator: WRONG
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    name
                  operator: ANOTHER WRONG
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "conditions[0]: The field \"name\" cannot be filtered or cannot be used with operator \"WRONG\"" has been thrown
    And an exception with message "conditions[0]: The field \"name\" cannot be filtered or cannot be used with operator \"ANOTHER WRONG\"" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
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
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    When the following yaml file is imported:
    """
    rules:
        canon_beautiful_description:
            conditions:
                - field:    wrong
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: A beautiful description
        sony_beautiful_description:
            conditions:
                - field:    another wrong
                  operator: CONTAINS
                  value:    Canon
                  locale:   en_US
            actions:
                - type:  set
                  field: description
                  value: The new Sony description

    """
    Then an exception with message "conditions[0]: The field \"wrong\" cannot be filtered or cannot be used with operator \"CONTAINS\"" has been thrown
    And an exception with message "conditions[0]: The field \"another wrong\" cannot be filtered or cannot be used with operator \"CONTAINS\"" has been thrown
    And the rule list contains the rules:
      """
      sony_beautiful_description:
        priority: 10
        conditions:
          - field:    name
            operator: CONTAINS
            value:    Canon
            locale:   en_US
        actions:
          - type:   set
            field:  description
            value:  Another good description
            locale: en_US
            scope:  tablet
      """
    And the rule list does not contain the "canon_beautiful_description" rule
