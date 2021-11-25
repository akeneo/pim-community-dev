@acceptance-back
Feature: Create a table attribute
  In order to structure my catalog
  As a catalog manager
  I need to be able to create a table attribute

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR

  Scenario: Can create a table attribute
    When I create a table attribute with a valid configuration
    Then There is no violation

  Scenario: Cannot create a table attribute without table configuration
    When I create a table attribute without table configuration
    Then There is a violation with message: The table attribute configuration must be filled

  Scenario: Cannot create a table configuration with only one column
    When I create a table attribute with a configuration with only one column
    Then There is a violation with message: The table attribute must contain at least 2 columns

  Scenario: Cannot create a table configuration with too many columns
    When I create a table attribute with a configuration with too many columns
    Then There is a violation with message: You have reached the maximum number of columns in your table (10)

  Scenario: Cannot create a table configuration without column code
    When I create a table attribute with a configuration '{"data_type": "text"}'
    Then There is a violation with message: The "code" column must be filled

  Scenario: Cannot create a table configuration with invalid column code
    When I create a table attribute with a configuration having column code "wrong code"
    Then There is a violation with message: The column code can only contain letters, numbers and underscores

  Scenario: Cannot create a table configuration with "product" code
    When I create a table attribute with a configuration having column code "product"
    Then There is a violation with message: This column code is not available

  Scenario: Cannot create a table configuration with "product_model" code
    When I create a table attribute with a configuration having column code "product_model"
    Then There is a violation with message: This column code is not available

  Scenario: Cannot create a table configuration with "attribute" code
    When I create a table attribute with a configuration having column code "attribute"
    Then There is a violation with message: This column code is not available

  Scenario: Cannot create a table configuration with blank column code
    When I create a table attribute with a configuration having column code ""
    Then There is a violation with message: The column code must be filled

  Scenario: Cannot create a table configuration with duplicate column code
    When I create a table attribute with a configuration having column code "ingredients"
    Then There is a violation with message: Each column requires a unique code. "ingredients" is already used

  Scenario: Cannot create a table configuration with duplicate column codes with case insensitive
    When I create a table attribute with a configuration having column codes "INGredients,ingredieNTS"
    Then There is a violation with message: Each column requires a unique code. "INGredients, ingredieNTS" are already used

  Scenario: Cannot create a table configuration with too long code
    When I create a table attribute with a configuration having column code "ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients"
    Then There is a violation with message: The column code is too long: it must be 100 characters or less

  Scenario: Cannot create a table configuration with too long label
    When I create a table attribute with a configuration having a label with 251 characters
    Then There is a violation with message: The column label is too long: it must be 250 characters or less

  Scenario: Cannot create a table configuration without type
    When I create a table attribute with a configuration '{"code": "quantity"}'
    Then There is a violation with message: The "data_type" column must be filled

  @only-ge
  Scenario: Cannot create a table configuration having unknown type
    When I create a table attribute with a configuration '{"data_type": "unknown", "code": "quantity"}'
    Then There is a violation with message: The column data type is unknown. Please choose one of the following: text, number, boolean, select

  @only-ee
  Scenario: Cannot create a table configuration having unknown type
    When I create a table attribute with a configuration '{"data_type": "unknown", "code": "quantity"}'
    Then There is a violation with message: The column data type is unknown. Please choose one of the following: text, number, boolean, select, record

  Scenario: Cannot create a table configuration having invalid type
    When I create a table attribute with a configuration '{"data_type": 1, "code": "quantity"}'
    Then There is a violation with message: The column data type must be a string

  Scenario: Cannot create a table configuration with invalid column labels format
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "labels": "A label without locale"}'
    Then There is a violation with message: The column labels must be a key/value object

  Scenario: Cannot create a table configuration with non activated locale
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "labels": { "pt_DTC": "a label" }}'
    Then There is a violation with message: The "pt_DTC" locale doesn't exist or is not activated

  Scenario: Cannot create a table configuration having invalid validation type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": 123}'
    Then There is a violation with message: The Validations field requires a key value object

  Scenario: Cannot create a table configuration having unknown validation
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "unknown": 123 }}'
    Then There is a violation with message: The "unknown" Validations does not work on a "text" column, allowed: "max_length"

  Scenario: Cannot create a table configuration having invalid max length validation value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max_length": "foo bar"}}'
    Then There is a violation with message: The required value is an integer

  Scenario: Cannot create a table configuration having invalid negative max_length
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max_length": -8000}}'
    Then There is a violation with message: The required value is a positive integer

  Scenario: Cannot create a table configuration having invalid min validation value type
    When I create a table attribute with a configuration '{"data_type": "number", "code": "quantity", "validations": { "min": "foo bar"}}'
    Then There is a violation with message: The required value is a number

  Scenario: Cannot create a table configuration having invalid max validation value type
    When I create a table attribute with a configuration '{"data_type": "number", "code": "quantity", "validations": { "max": "foo bar"}}'
    Then There is a violation with message: The required value is a number

  Scenario: Cannot create a non table attribute with a table configuration
    When I create a text attribute with a table configuration
    Then There is a violation with message: The type pim_catalog_text does not allow table_configuration

  Scenario: Cannot create a table attribute when the first column is not select
    When I create a table attribute with text first column
    Then There is a violation with message: The first column type should always be "select", current type: "text"

  Scenario: Cannot create a table configuration having invalid decimals allowed value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "decimals_allowed": "error"}}'
    Then There is a violation with message: The required value is a boolean

  Scenario: Cannot create a table configuration with min validation greater than max validation
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "min": 10, "max": 5}}'
    Then There is a violation with message: The maximum value should be greater than the minimum value.

  Scenario: Cannot create a table configuration with invalid validations on a text column
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "min": 10, "max": 20, "decimals_allowed": true }}'
    Then There is a violation with message: The "min, max, decimals_allowed" Validations does not work on a "text" column, allowed: "max_length"

  Scenario: Cannot create a table configuration with a min or max validation greater than 100
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max_length": 200 }}'
    Then There is a violation with message: Please define a maximum number of characters lower than or equal to 100.

  Scenario: Cannot create a table configuration if options is not an array
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": "test"}'
    Then There is a violation with message: The Options field requires an array containing only key value objects

  Scenario: Cannot create a table configuration with an invalid option type
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": ["test"]}'
    Then There is a violation with message: The Options field requires an array containing only key value objects

  Scenario: Cannot create a table configuration with an option without code
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"labels": []}]}'
    Then There is a violation with message: The "code" is required

  Scenario: Cannot create a table configuration with an option with an invalid code type
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": false}]}'
    Then There is a violation with message: The option code can only contain letters, numbers and underscores

  Scenario: Cannot create a table configuration with an option with an empty code
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": ""}]}'
    Then There is a violation with message: The option code must be filled

  Scenario: Cannot create a table configuration with an option with invalid labels
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": "sugar", "labels": "invalid_type"}]}'
    Then There is a violation with message: The option labels must be a key/value object

  Scenario: Cannot create a table configuration with an option with an invalid label type
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": "sugar", "labels": {"en_US": false}}]}'
    Then There is a violation with message: The option label must be a string

  Scenario: Cannot create a table configuration with an option with an invalid label locale
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": "sugar", "labels": {"pt_DTC": "label"}}]}'
    Then There is a violation with message: The "pt_DTC" locale doesn't exist or is not activated

  Scenario: Cannot create a table configuration with an option with a tool long label locale
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "options": [{"code": "sugar", "labels": {"en_US": "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."}}]}'
    Then There is a violation with message: The option label is too long: it must be 255 characters or less

  Scenario: Cannot create a table configuration with options for a non select column
    When I create a table attribute with a configuration '{"data_type": "text", "code": "ingredient", "options": [{"code": "sugar", "labels": {"en_US": "Sugar"}}]}'
    Then There is a violation with message: Options cannot be set for a "text" column type

  Scenario: Cannot create a table configuration with an unknown column field
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "toto": "titi"}'
    Then There is a violation with message: Make sure you use only expected fields, current field: "toto"

  Scenario: Cannot create a table configuration with too many options
    When I create a table attribute with too much options
    Then There is a violation with message: You have reached the maximum number of options in your column (20000).

  Scenario: Cannot create a 51th table attribute
    Given 50 table attributes
    When I create a table attribute with a valid configuration
    Then There is a violation with message: You have reached the maximum number of table attributes within your PIM (50)

  Scenario: Cannot create a table configuration with a bad "is_required_for_completeness" value
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "is_required_for_completeness": "bad"}'
    Then There is a violation with message: The required value is a boolean

  Scenario: Cannot create a table configuration with a null "is required for completeness" value
    When I create a table attribute with a configuration '{"data_type": "select", "code": "ingredient", "is_required_for_completeness": null}'
    Then There is a violation with message: The "is_required_for_completeness" option requires a value

  @only-ge
  Scenario: Cannot create a table configuration with a record column
    When I create a table attribute with a configuration '{"data_type": "record", "code": "record", "is_required_for_completeness": true, "reference_entity_code": "brands"}'
    Then There is a violation with message: The column data type is unknown. Please choose one of the following: text, number, boolean, select

  @only-ee
  Scenario: Can create a table configuration with a record column
    When I create a table attribute with a configuration '{"data_type": "record", "code": "record", "is_required_for_completeness": true, "reference_entity_code": "brands"}'
    Then There is no violation

# @TODO : Move record tests to EE
  @only-ee
  Scenario: Cannot create a table configuration with a null "reference_entity_code" in a record column
    When I create a table attribute with a configuration '{"data_type": "record", "code": "record", "is_required_for_completeness": true, "reference_entity_code": null}'
    Then There is a violation with message: The "reference_entity_code" option requires a value

  @only-ee
  Scenario: Cannot create a table configuration with an invalid "reference_entity_code" type in a record column
    When I create a table attribute with a configuration '{"data_type": "record", "code": "record", "is_required_for_completeness": true, "reference_entity_code": 153}'
    Then There is a violation with message: The required value is a string
