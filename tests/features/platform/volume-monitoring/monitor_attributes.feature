Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes

  @acceptance-back
  Scenario: Monitor the number of attributes
    Given a catalog with 99 attributes
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of attributes is 99
