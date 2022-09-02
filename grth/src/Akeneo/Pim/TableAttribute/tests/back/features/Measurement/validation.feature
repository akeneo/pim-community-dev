@acceptance-back
Feature: Maintain consistency with measurement family
  In order to keep my catalog consistent
  As a product manager
  I need to to know why I can't delete or update a measurement family when it is linked to a column

  Background:
    Given the "duration" measurement family with the "second,minute" units

  Scenario: Can delete a measurement family not linked to a table attribute column
    When I delete the "duration" measurement family
    Then There is no violation
    And The "duration" measurement family was deleted

  Scenario: Cannot delete a measurement family linked to a table attribute column
    Given a table attribute with measurement column from "duration" family code and "second" default unit code
    When I delete the "duration" measurement family
    Then There is a violation with message: This measurement family is used in a product attribute, therefore it cannot be removed.
    And The "duration" measurement family was not deleted

  Scenario: Cannot delete a measurement family linked to a table attribute column with case insensitive
    Given a table attribute with measurement column from "duration" family code and "second" default unit code
    When I delete the "DurATION" measurement family
    Then There is a violation with message: This measurement family is used in a product attribute, therefore it cannot be removed.
    And The "duration" measurement family was not deleted

  Scenario: Cannot delete a measurement family unit when the measurement family is linked to a table column
    Given a table attribute with measurement column from "duration" family code and "second" default unit code
    When I remove the "minute" unit from the "duration" family
    Then There is a violation with message: This measurement family unit is used in a product attribute. You can only edit the translated labels and symbol of a unit.
    And The "duration" measurement family contains the "minute" unit

  Scenario: Cannot update a measurement family unit operation when the measurement family is linked to a table column
    Given a table attribute with measurement column from "duration" family code and "second" default unit code
    When I add a step for the "minute" conversion operation in the "duration" family
    Then There is a violation with message: This measurement family unit is used in a product attribute. You can only edit the translated labels and symbol of a unit.

  Scenario: Can add units and update labels of a measurement family linked to a table column
    Given a table attribute with measurement column from "duration" family code and "second" default unit code
    When I add the "hour" unit and update the labels of the "duration" family to '{"en_US": "Duration"}'
    Then There is no violation
    And The "duration" measurement family contains the "hour" unit
    And The labels of the "duration" family were updated to '{"en_US": "Duration"}'
