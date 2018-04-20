Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of categories

  @acceptance-back
  Scenario: Monitor the number of categories
    Given a catalog with 5 categories
    And the limit of the number of categories is set to 6
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of categories is 5
    And the report does not warn the users that the number of categories is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of categories is high
    Given a catalog with 6 categories
    And the limit of the number of categories is set to 5
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of categories is high
