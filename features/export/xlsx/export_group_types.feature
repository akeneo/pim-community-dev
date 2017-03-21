@javascript
Feature: Export group types
  In order to be able to access and modify group types data outside PIM
  As an administrator
  I need to be able to export group types in xlsx format

  Scenario: Successfully export group types in xlsx with headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_group_type_export" configuration:
      | filePath | %tmp%/xlsx_footwear_group_type_export/xlsx_footwear_group_type_export.xlsx |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_group_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_group_type_export" job to finish
    Then exported xlsx file of "xlsx_footwear_group_type_export" should contain:
     | code    | label-en_US | is_variant |
     | VARIANT | [VARIANT]   | 1          |
     | RELATED | [RELATED]   | 0          |
     | XSELL   | [XSELL]     | 0          |

  Scenario: Successfully export group types in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_group_type_export" configuration:
      | filePath   | %tmp%/xlsx_footwear_group_type_export/xlsx_footwear_group_type_export.xlsx |
      | withHeader | no                                                                         |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_group_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_group_type_export" job to finish
    Then exported xlsx file of "xlsx_footwear_group_type_export" should contain:
      | VARIANT | [VARIANT] | 1 |
      | RELATED | [RELATED] | 0 |
      | XSELL   | [XSELL]   | 0 |
