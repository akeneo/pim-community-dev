Feature: Export groups
  In order to be able to access and modify groups data outside PIM
  As Julia
  I need to be able to export groups

  @javascript
  Scenario: Successfully export groups
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "footwear_group_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then file "/tmp/group_export/group_export.csv" should contain 3 rows
