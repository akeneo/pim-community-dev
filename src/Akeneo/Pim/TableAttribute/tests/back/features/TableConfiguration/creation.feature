@acceptance-back
Feature: Create a table attribute
  In order to structure my catalog
  As a catalog manager
  I need to be able to create a table attribute

  Scenario: Can create a table attribute
    When I create a table attribute with a valid configuration
    Then There is no violation

  Scenario: Cannot create a table attribute without table configuration
    When I create a table attribute without table configuration
    Then There is a violation with message: TODO error message

  Scenario: Cannot create a table configuration with only one column
    When I create a table attribute with a configuration with only one column
    Then There is a violation with message: TODO another error message

  Scenario: Cannot create a table configuration without column code
    When I create a table attribute with a configuration without column code
    Then There is a violation with message: TODO yet another error message: "code"

  Scenario: Cannot create a table configuration with invalid column code
    When I create a table attribute with a configuration having column code "wrong code"
    Then There is a violation with message: TODO bad code

  Scenario: Cannot create a table configuration with blank column code
    When I create a table attribute with a configuration having column code ""
    Then There is a violation with message: TODO code blank

  Scenario: Cannot create a table configuration with duplicate column code
    When I create a table attribute with a configuration having column code "ingredients"
    Then There is a violation with message: TODO IsColumnCodeUnique message ingredients

  # TODO Add tests about missing type or wrong type or wrong labels format
