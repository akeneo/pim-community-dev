Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of variant products

  @acceptance-back
  Scenario: Monitor the number of variant products
    Given a catalog with 10 variant products
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of variant products is 10
