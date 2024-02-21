Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of locales

  @acceptance-back
  Scenario: Monitor the number of locales
    Given a catalog with 5 locales
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of locales is 5
