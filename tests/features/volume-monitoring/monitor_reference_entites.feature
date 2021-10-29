Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of reference entities

  @acceptance-back
  Scenario: Monitor the number of families
    Given a catalog with 10 reference entities
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of reference entities is 10
