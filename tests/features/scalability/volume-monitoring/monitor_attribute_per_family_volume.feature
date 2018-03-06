Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes per family

  Scenario: Monitor the number of attributes per family
    Given a families with 10 attributes
    And a family with 4 attributes
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the mean number of attributes per family is 7
    And the report returns that the maximum number of attributes per family is 10

  Scenario: Warn the user administrator when the number of attributes per family has passed the limit
    Given a families with 10 attributes
    And a family with 4 attributes
    And the limit of the maximum number of attributes per family is set to 8
    And the limit of the mean number of attributes per family is set to 5
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the limit of maximum number of attributes per family has been passed
    And the report warns the users that the limit of the mean number of attributes per family has been passed
