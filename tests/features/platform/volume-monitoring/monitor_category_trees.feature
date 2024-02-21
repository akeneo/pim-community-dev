Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of category trees

  @acceptance-back
  Scenario: Monitor the number of category trees
    Given a catalog with 3 category trees
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of category trees is 3
