Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of categories

  @acceptance-back
  Scenario: Monitor the number of categories
    Given a catalog with 5 categories
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of categories is 5
