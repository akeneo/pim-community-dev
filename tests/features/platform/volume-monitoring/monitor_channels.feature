Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of channels

  @acceptance-back
  Scenario: Monitor the number of channels
    Given a catalog with 5 channels
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of channels is 5
