@acceptance-back
Feature: Update a table attribute
  In order to structure my catalog
  As a catalog manager
  I need to be able to update a table attribute

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR

  Scenario: Can update a table attribute
    Given a valid table attribute
    When I update a table attribute with a valid configuration
    Then There is no violation
    And the attribute contains the "new_column" column
    And the attribute does not contain the "isAllergenic" column

  Scenario: Cannot update a table attribute when the first column code changes
    Given a valid table attribute
    When I change a table attribute updating the first column code
    Then There is a violation with message: The code of your first column cannot be changed.

  Scenario: Can update the 50th table attribute
    Given 49 table attributes
    And a valid table attribute
    When I update a table attribute with a valid configuration
    Then There is no violation

  @only-ee
  Scenario: Cannot update the identifier of a reference entity column
    Given the brands reference entity
    And a table attribute with the following configuration '{"data_type": "reference_entity", "code": "brand", "reference_entity_identifier": "brands"}'
    When I update the reference entity identifier of the "brand" column
    Then There is a violation with message: The reference_entity_identifier cannot be updated

  @only-ee
  Scenario: Can update a reference entity column definition when keeping the same reference entity identifier
    Given the brands reference entity
    And a table attribute with the following configuration '{"data_type": "reference_entity", "code": "brand", "reference_entity_identifier": "brands"}'
    When I update the "en_US" label of the "brand" column as "Brand"
    Then There is no violation
    And the "en_US" label of the "brand" column should be "Brand"

  Scenario: Cannot update the measurement family of an existing measurement column
    Given the duration measurement family with the second,minute units
    And the length measurement family with the meter,centimeter units
    And a valid table attribute with a measurement column using duration measurement family and second default unit code
    When I update the measurement family code with length value
    Then There is a violation with message: The measurement_family_code cannot be updated

  Scenario: Can update the measurement default unit of an existing measurement column
    Given the duration measurement family with the second,minute units
    And a valid table attribute with a measurement column using duration measurement family and second default unit code
    When I update the measurement default unit code with minute value
    Then There is no violation
