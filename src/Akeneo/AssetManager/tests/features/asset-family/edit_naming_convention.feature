Feature: Edit the naming convention of an asset family
  In order to automatically enrich asset properties
  As a user
  I want to be able to define naming convention for an asset family

  @acceptance-back
  Scenario: Set naming convention to an asset family
    Given an asset family
    When the user edits the family to set a valid naming convention
    Then the family naming convention should be set
