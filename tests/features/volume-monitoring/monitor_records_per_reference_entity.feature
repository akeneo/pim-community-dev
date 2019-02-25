Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of records per reference entities

  @acceptance-back
  Scenario: Monitor the max number of records per reference entity
    Given a catalog with maximum 10 records per reference entity
    And the max limit of records per reference entity is set to 11
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the max number of records per reference entityis 10
    And the report does not warn the users that the max number of records per reference entity is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of records per reference entity is high
    Given a catalog with maximum 11 records per reference entity
    And the max limit of records per reference entity is set to 10
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the max number of records per reference entity is high

  @acceptance-back
  Scenario: Monitor the average number of records per reference entity
    Given a catalog with on average 10 records per reference entity
    And the average limit of records per reference entity is set to 11
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of records per reference entityis 10
    And the report does not warn the users that the average number of records per reference entity is high

  @acceptance-back
  Scenario: Warn the user administrator when the average number of records per reference entity is high
    Given a catalog with on average 11 records per reference entity
    And the average limit of records per reference entity is set to 10
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the average number of records per reference entity is high
