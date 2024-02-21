Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of options per attribute

  @acceptance-back
  Scenario: Monitor the number of options per attribute
    Given an attribute with 10 options
    And an attribute with 4 options
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the average number of options per attribute is 7
    And the report returns that the maximum number of options per attribute is 10
