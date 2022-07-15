@javascript
Feature: Export groups
  In order to be able to access and modify groups data outside PIM
  As a product manager
  I need to be able to export groups

  Scenario: Successfully export groups
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_group_export" configuration:
      | filePath | %tmp%/group_export/group_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_group_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_group_export" job to finish
    Then I should see the text "Read 1"
    And I should see the text "Written 1"
    And exported file of "csv_footwear_group_export" should contain:
    """
    code;type;label-en_US
    similar_boots;RELATED;"Similar boots"
    """
