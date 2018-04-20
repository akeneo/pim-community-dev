Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of locales

  @acceptance-back
  Scenario: Monitor the number of locales
    Given a catalog with 5 locales
    And the limit of the number of locales is set to 6
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of locales is 5
    And the report does not warn the users that the number of locales is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of locales is high
    Given a catalog with 6 locales
    And the limit of the number of locales is set to 5
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of locales is high
