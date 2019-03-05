Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of records per reference entities

  @acceptance-back
  Scenario: Monitor the max number of records per reference entity
    Given a reference entity with 10 records
    And a reference entity with 4 records
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of records per reference entity is 7
    And the report returns that the maximum number of records per reference entity is 10
