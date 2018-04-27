Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of attributes

  Scenario: Monitor the number of attributes
    Given 5 localizable attributes
    And 4 scopable attributes
    And 3 localizable and scopable attributes
    And 2 attributes neither localizable nor scopable
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns a total of 14 attributes
    And the report returns 5 localizable attributes
    And the report returns 4 scopable attributes
    And the report returns 3 localizable and scopable attributes

  Scenario: Warn the user administrator when the number of attributes is high
    Given 5 localizable attributes
    And 4 scopable attributes
    And 3 localizable and scopable attributes
    And 2 attributes neither localizable nor scopable
    And the limit of the number of localizable attributes is set to 4
    And the limit of the number of scopable attributes is set to 3
    And the limit of the number of localizable and scopable attributes is set to 2
    And the limit of the total number of attributes is set to 2
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns warn the users that the total number of attributes is high
    And the report warns the users the number of localizable attributes is high
    And the report warns the users the number of scopable attributes is high
    And the report warns the users the number of localizable and scopable attributes is high
