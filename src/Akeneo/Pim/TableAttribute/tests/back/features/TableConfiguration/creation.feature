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

  Scenario: Cannot create a table configuration without column code
    When I create a table attribute with a configuration '{"data_type": "text"}'
    Then There is a violation with message: The "code" column must be filled

  Scenario: Cannot create a table configuration with invalid column code
    When I create a table attribute with a configuration having column code "wrong code"
    Then There is a violation with message: The column code can only contain letters, numbers and underscores

  Scenario: Cannot create a table configuration with blank column code
    When I create a table attribute with a configuration having column code ""
    Then There is a violation with message: The column code must be filled

  Scenario: Cannot create a table configuration with duplicate column code
    When I create a table attribute with a configuration having column code "ingredients"
    Then There is a violation with message: Each column requires a unique code. "ingredients" is already used

  Scenario: Cannot create a table configuration with too long code
    When I create a table attribute with a configuration having column code "ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients_ingredients"
    Then There is a violation with message: The column code is too long: it must be 100 characters or less

  Scenario: Cannot create a table configuration without type
    When I create a table attribute with a configuration '{"code": "quantity"}'
    Then There is a violation with message: The "data_type" column must be filled

  Scenario: Cannot create a table configuration having unknown type
    When I create a table attribute with a configuration '{"data_type": "unknown", "code": "quantity"}'
    Then There is a violation with message: The column data type is unknown. Please choose one of the following: number, text, select, boolean

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
    Then There is a violation with message: TODO Validation should be an object

  Scenario: Cannot create a table configuration having unknown validation
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "unknown": 123 }}'
    Then There is a violation with message: TODO unknown validation : "unknown". Authorized: max_mength, min, max, decimals_allowed

  Scenario: Cannot create a table configuration having invalid max length validation value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max_length": "foo bar"}}'
    Then There is a violation with message: TODO integer

  Scenario: Cannot create a table configuration having invalid negative max_length
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max_length": -8000}}'
    Then There is a violation with message: TODO positive int

  Scenario: Cannot create a table configuration having invalid min validation value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "min": "foo bar"}}'
    Then There is a violation with message: TODO integer

  Scenario: Cannot create a table configuration having invalid negative min
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "min": -8000}}'
    Then There is a violation with message: TODO positive integer

  Scenario: Cannot create a table configuration having invalid max length validation value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max": "foo bar"}}'
    Then There is a violation with message: TODO integer

  Scenario: Cannot create a table configuration having invalid negative max
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "max": -8000}}'
    Then There is a violation with message: TODO positive integer

  Scenario: Cannot create a non table attribute with a table configuration
    When I create a text attribute with a table configuration
    Then There is a violation with message: The type pim_catalog_text does not allow table_configuration

  Scenario: Cannot create a table attribute when the first column is not select
    When I create a table attribute with text first column
    Then There is a violation with message: TODO The first column should be select, "text" given

  Scenario: Cannot create a table configuration having invalid decimals allowed value type
    When I create a table attribute with a configuration '{"data_type": "text", "code": "quantity", "validations": { "decimals_allowed": "error"}}'
    Then There is a violation with message: TODO bool
