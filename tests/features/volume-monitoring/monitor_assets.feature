Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of assets

#  @acceptance-back
  Scenario: Monitor the number of assets
    Given a catalog with 44 assets
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of assets is 44
