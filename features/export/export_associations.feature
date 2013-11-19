Feature: Export associations
  In order to be able to access and modify associations data outside PIM
  As Julia
  I need to be able to export associations

  @javascript
  Scenario: Successfully export associations
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "footwear_association_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then file "/tmp/association_export/association_export.csv" should contain 5 rows
