Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of scopable attributes

  @acceptance-back
  Scenario: Monitor the number of scopable attributes
    Given a catalog with 20 scopable attributes
    And the limit of the number of scopable attributes is set to 21
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of scopable attributes is 20
    And the report does not warn the users that the number of scopable attributes is high

  @acceptance-back
  Scenario: Warn the user administrator when the maximum number of scopable attributes is high
    Given a catalog with 21 scopable attributes
    And the limit of the number of scopable attributes is set to 20
    When the administrator user asks for the catalog volume monitoring report
    Then the report warns the users that the number of scopable attributes is high
