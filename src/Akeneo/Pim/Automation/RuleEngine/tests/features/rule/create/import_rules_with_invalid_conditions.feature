Feature: Import rules
  In order to fix incorrect product data
  As an administrator
  I need to know which rules are incorrect and why

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Skip rules with unsupported value type for text attribute in conditions
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
    """
    Then an exception with message "conditions[0]: Property \"name\" expects a string as data, \"integer\" given." has been thrown
    And an exception with message "actions[0]: The name attribute requires a string, a integer was detected." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported integer value for attribute of type textarea in conditions
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
    """
    Then an exception with message "conditions[0]: Property \"description\" expects a string as data, \"integer\" given." has been thrown
    And an exception with message "actions[0]: The description attribute requires a string, a integer was detected." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported integer value for attribute of type identifier in conditions
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
    """
    Then an exception with message "conditions[0]: Property \"sku\" expects a string as data, \"integer\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported string value for attribute of type simple select in conditions
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
    """
    Then an exception with message "conditions[0]: Property \"manufacturer\" expects an array as data, \"string\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported string value for the multi select attribute of type multi select in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"weather_conditions\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"weather_conditions\" expects an array as data, \"string\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported array values for attribute of type multi select in conditions
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
    """
    Then an exception with message "conditions[0]: Object \"options\" with code \"invalid\" does not exist" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type price collection in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"price\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"price\" expects an array as data, \"string\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type metric in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"length\" expects an array as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: Property \"length\" expects an array as data, \"string\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type number in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"number_in_stock\" expects a numeric as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: The number_in_stock attribute requires a number, and the submitted invalid value is not." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type boolean in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"handmade\" expects a boolean as data, \"string\" given." has been thrown
    And an exception with message "actions[0]: The handmade attribute requires a boolean value (true or false) as data, a string was detected." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type date in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"release_date\" expects a string with the format \"yyyy-mm-dd\" as data, \"invalid\" given." has been thrown
    And an exception with message "actions[0]: The release_date attribute requires a valid date. Please use the following format yyyy-mm-dd for dates." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with unsupported values for attribute of type media in conditions and actions
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
    """
    Then an exception with message "conditions[0]: Property \"side_view\" expects a string as data, \"array\" given." has been thrown
    And an exception with message "actions[0]: Property \"side_view\" expects a valid pathname as data, \"invalid/path/to/image\" given." has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with array values for attribute of type media in conditions and actions
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
    """
    Then an exception with message "conditions[0]: The \"value\" key is missing or empty" has been thrown
    And an exception with message "actions[0]: Property \"side_view\" expects a string as data, \"array\" given" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing conditions key
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
    """
    Then an exception with message "conditions: The \"conditions\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing operator key for conditions
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
    """
    Then an exception with message "conditions[0].operator: The \"operator\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing field key for conditions
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
    """
    Then an exception with message "conditions[0].field: The \"field\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with missing value key for conditions
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
    """
    Then an exception with message "conditions[0]: The \"value\" key is missing or empty" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid operator for conditions
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
                  locale: en_US
                  scope: tablet
                  value: A beautiful description
    """
    Then an exception with message "conditions[0]: The \"name\" field cannot be filtered or cannot be used with the \"WRONG\" operator" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with non existing field for conditions
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
                  scope: tablet
                  locale: en_US
    """
    Then an exception with message "conditions[0]: The \"wrong\" field cannot be filtered or cannot be used with the \"CONTAINS\" operator" has been thrown
    And the rule list does not contain the "canon_beautiful_description" rule

  @integration-back
  Scenario: Skip rules with invalid date format for updated
    When the following yaml file is imported:
    """
    rules:
      disable_recent_products:
        priority: 10
        conditions:
          - field:    updated
            operator: <
            value:    '1 day'
          - field: created
            operator: '>'
            value: '-1.5 week'
        actions:
          - type:   set
            field:  enabled
            value:  true
    """
    Then an exception with message "conditions[0]: Property \"updated\" expects a string with the format \"yyyy-mm-dd H:i:s\" as data, \"1 day\" given" has been thrown
    And an exception with message "conditions[1]: Property \"created\" expects a string with the format \"yyyy-mm-dd H:i:s\" as data, \"-1.5 week\" given" has been thrown
    And the rule list does not contain the "disable_old_products" rule

  @integration-back
  Scenario: Skip rules with null value for categories in conditions
    When the following yaml file is imported:
    """
    rules:
        rule_with_invalid_null_category:
            conditions:
                - field:    categories
                  operator: UNCLASSIFIED
                  value:
                      - test1
                      - null
                      - test2
            actions:
                - type:  set
                  field: name
                  value: Test
                  locale: en_US
    """
    Then an exception with message "conditions[0].value[1]: This value should not be null." has been thrown
    And the rule list does not contain the "rule_with_null_category" rule
