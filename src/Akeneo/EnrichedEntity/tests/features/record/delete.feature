Feature: Delete one record
  In order to administrate records
  As a user
  I need to delete records

  @acceptance-back
  Scenario: Deleting a record
    Given an enriched entity with two records
    When the user deletes the first record
    Then there is no exception thrown
#    And there is no violations errors
    And the first record should not exist anymore

  @acceptance-back
  Scenario: Deleting a unknown record
    Given an enriched entity with two records
    When the user deletes a wrong record
    Then an exception is thrown
