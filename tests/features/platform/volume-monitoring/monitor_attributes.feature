Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes

  @acceptance-back
  Scenario: Monitor the number of attributes
    Given a catalog with 99 attributes
    And the limit of the number of attributes is set to 100
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of attributes is 99
    And the report does not warn the users that the number of attributes is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of attributes is high
    Given a catalog with 101 attributes
    And the limit of the number of attributes is set to 100
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of attributes is high
