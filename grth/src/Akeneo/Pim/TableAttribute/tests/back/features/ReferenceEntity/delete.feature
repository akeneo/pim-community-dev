@acceptance-back @only-ee
Feature: Delete reference entity
  In order to delete a reference entity
  As a product manager
  I need to be able to know when the reference entity is linked to a column

  Background:
    Given the "brands" reference entity

  Scenario: Can delete a reference entity not linked to a table attribute column
    When I delete the "brands" reference entity
    Then There is no violation
    And The "brands" reference entity was deleted

  Scenario: Cannot delete a reference entity linked to a table attribute column
    Given I create a table attribute with "brands" reference entity link column
    When I delete the "brands" reference entity
    Then There is a violation with message: This reference entity is used in a product attribute, therefore it cannot be removed.
    And The "brands" reference entity was not deleted

  Scenario: Cannot delete a reference entity linked to a table attribute column with case insensitive
    Given I create a table attribute with "BRAnds" reference entity link column
    When I delete the "brands" reference entity
    Then There is a violation with message: This reference entity is used in a product attribute, therefore it cannot be removed.
    And The "brands" reference entity was not deleted
