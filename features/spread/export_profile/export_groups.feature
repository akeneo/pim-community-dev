Feature: Export groups
  In order to be able to access and modify groups data outside PIM
  As a product manager
  I need to be able to export groups (not variant groups)

  @javascript
  Scenario: Successfully export groups
    Given a "footwear" catalog configuration
    And the following job "footwear_group_export" configuration:
      | filePath | %tmp%/group_export/group_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_group_export" export job page
    When I launch the export job
    And I wait for the "footwear_group_export" job to finish
    Then I should see "Read 1"
    And I should see "Written 1"
    And exported file of "footwear_group_export" should contain:
    """
    code;type;label-en_US
    similar_boots;RELATED;"Similar boots"
    """
