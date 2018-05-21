Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of families

  @acceptance-back
  Scenario: Monitor the number of families
    Given a catalog with 10 families
    And the limit of the number of families is set to 11
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of families is 10
    And the report does not warn the users that the number of families is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of families is high
    Given a catalog with 11 families
    And the limit of the number of families is set to 10
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of families is high
