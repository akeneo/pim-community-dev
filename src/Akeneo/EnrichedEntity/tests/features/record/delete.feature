Feature: Delete one record
  In order to administrate records
  As a user
  I need to delete records

  @acceptance-back
  Scenario: Deleting a record
    Given an enriched entity with one record
    When the user deletes the record
    Then there is no exception thrown
    And there is no violations errors
    And the record should not exist anymore

  @acceptance-back
  Scenario: Deleting a unknown record
    When the user tries to delete record that does not exist
    Then an exception is thrown
