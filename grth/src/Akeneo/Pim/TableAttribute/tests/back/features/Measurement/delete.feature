@acceptance-back @only-ee
Feature: Delete measurement family
  In order to delete a measurement family
  As a product manager
  I need to be able to know when the measurement family is linked to a column

  Background:
    Given the "duration" measurement family with the "second" units

  Scenario: Can delete a measurement family not linked to a table attribute column
    When I delete the "duration" measurement family
    Then There is no violation
    And The "duration" measurement family was deleted

  Scenario: Cannot delete a measurement family linked to a table attribute column
    Given I create a table attribute with measurement column from "duration" family code and "second" default unit code
    When I delete the "duration" measurement family
    Then There is a violation with message: This measurement family is used in a product attribute, therefore it cannot be removed.
    And The "duration" measurement family was not deleted

  Scenario: Cannot delete a measurement family linked to a table attribute column with case insensitive
    Given I create a table attribute with measurement column from "DurATION" family code and "second" default unit code
    When I delete the "duration" measurement family
    Then There is a violation with message: This measurement family is used in a product attribute, therefore it cannot be removed.
    And The "duration" measurement family was not deleted
