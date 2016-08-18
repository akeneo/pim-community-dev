Feature: Export group types
  In order to be able to access and modify group types data outside PIM
  As an administrator
  I need to be able to export group types

  @javascript
  Scenario: Successfully export group types
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_group_type_export" configuration:
      | filePath | %tmp%/group_type_export/group_type_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_group_type_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_group_type_export" job to finish
    Then I should see "Read 3"
    And I should see "Written 3"
    And exported file of "csv_footwear_group_type_export" should contain:
    """
    code;label-en_US;is_variant
    VARIANT;[VARIANT];1
    RELATED;[RELATED];0
    XSELL;[XSELL];0
    """
