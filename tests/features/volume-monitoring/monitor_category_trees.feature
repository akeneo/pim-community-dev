Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of category trees

  @acceptance-back
  Scenario: Monitor the number of category trees
    Given a catalog with 3 category trees
    And the limit of the number of category trees is set to 5
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of category trees is 3
    And the report does not warn the users that the number of category trees is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of category trees is high
    Given a catalog with 6 category trees
    And the limit of the number of category trees is set to 5
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of category trees is high
