Feature: Monitor catalog volume
  In order to guarantee the performance of the PIM
  As an administrator user
  I want to monitor the volume of asset category trees

#  @acceptance-back
  Scenario: Monitor the number of asset category trees
    Given a catalog with 3 asset category trees
    When the administrator user asks for the catalog volume monitoring report
    Then the report returns that the number of asset category trees is 3
