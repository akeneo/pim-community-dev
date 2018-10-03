Feature: Delete all reference entity record
  In order to administrate records
  As a user
  I need to delete all records belonging to a refenrence entity

  @acceptance-back
  Scenario: Deleting all records of a reference entity
    Given two reference entities with two records each
    When the user deletes all the records from one reference entity
    Then there should be no records for this reference entity
    But there is still two records on the other reference entity

  @acceptance-back
  Scenario: Deleting all records of an unknown reference entity
    Given two reference entities with two records each
    When the user deletes all the records from an unknown entity
    And there is still two records for each reference entity
