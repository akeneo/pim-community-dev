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
    When I create a table attribute with a configuration without column code
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
    When I create a table attribute with a configuration without type
    Then There is a violation with message: The "data_type" column must be filled

  Scenario: Cannot create a table configuration having unknown type
    When I create a table attribute with a configuration having unknown type
    Then There is a violation with message: The column data type is unknown. Please choose one of the following: number, text, select, boolean

  Scenario: Cannot create a table configuration having invalid type
    When I create a table attribute with a configuration having invalid type
    Then There is a violation with message: The column data type must be a string

  Scenario: Cannot create a table configuration with invalid column labels format
    When I create a table attribute with a configuration having invalid column labels format
    Then There is a violation with message: The column labels must be a key/value object

  Scenario: Cannot create a table configuration with non activated locale
    When I create a table attribute with a configuration having non activated locale
    Then There is a violation with message: The "pt_DTC" locale doesn't exist or is not activated

  Scenario: Cannot create a non table attribute with a table configuration
    When I create a text attribute with a table configuration
    Then There is a violation with message: The type pim_catalog_text does not allow table_configuration
