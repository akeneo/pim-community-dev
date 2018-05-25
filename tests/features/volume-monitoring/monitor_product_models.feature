Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of product models

  @acceptance-back
  Scenario: Monitor the number of product models
    Given a catalog with 10 product models
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of product models is 10

