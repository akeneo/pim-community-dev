Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of scopable attributes

  @acceptance-back
  Scenario: Monitor the number of scopable attributes
    Given a catalog with 20 scopable attributes
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of scopable attributes is 20
