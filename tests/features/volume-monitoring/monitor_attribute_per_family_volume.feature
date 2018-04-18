Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes per family

  @acceptance-back @acceptance-front
  Scenario: Monitor the number of attributes per family
    Given a family with 10 attributes
    And a family with 4 attributes
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of attributes per family is 7
    And the report returns that the maximum number of attributes per family is 10

  @acceptance-back @acceptance-front
  Scenario: Warn the user administrator when the maximum number of attributes per family is high
    Given a family with 8 attributes
    And a family with 2 attributes
    And the limit of the number of attributes per family is set to 6
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of attributes per family is high
