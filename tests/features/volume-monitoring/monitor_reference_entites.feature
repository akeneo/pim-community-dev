Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of reference entities

  @acceptance-back
  Scenario: Monitor the number of families
    Given a catalog with 10 reference entities
    And the limit of reference entities is set to 11
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of reference entities is 10
    And the report does not warn the users that the number of reference entities is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of reference entities is high
    Given a catalog with 11 reference entities
    And the limit of reference entities is set to 10
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of reference entities is high
