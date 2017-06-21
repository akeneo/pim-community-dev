@javascript
Feature: Export variant groups in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export variant groups in XLSX

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export variant groups in xlsx
    Given the following job "xlsx_variant_group_export" configuration:
      | filePath | %tmp%/xlsx_variant_group_export/xlsx_variant_group_export.xlsx |
    When I am on the "xlsx_variant_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_variant_group_export" job to finish
    Then exported xlsx file of "xlsx_variant_group_export" should contain:
      | code     | type    | axis                        | label-en_US | label-en_GB | label-fr_FR | label-de_DE |
      | tshirts  | variant | color,size                  | T-shirts    | T-shirts    | T-shirts    | T-Shirts    |
      | sweaters | variant | color,size                  | Sweaters    | Chandails   | Sweaters    | Pullovern   |
      | jackets  | variant | chest_size,color,waist_size | Jackets     | Jackets     | Vestes      | Jacken      |

  Scenario: Successfully export variant groups in xlsx without header
    Given the following job "xlsx_variant_group_export" configuration:
      | filePath   | %tmp%/xlsx_variant_group_export/xlsx_variant_group_export.xlsx |
      | withHeader | no                                                             |
    When I am on the "xlsx_variant_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_variant_group_export" job to finish
    Then exported xlsx file of "xlsx_variant_group_export" should contain:
      | tshirts  | variant | color,size                  | T-shirts | T-shirts  | T-shirts | T-Shirts  |
      | sweaters | variant | color,size                  | Sweaters | Chandails | Sweaters | Pullovern |
      | jackets  | variant | chest_size,color,waist_size | Jackets  | Jackets   | Vestes   | Jacken    |

  Scenario: Successfully export products into several files
    Given the following job "xlsx_variant_group_export" configuration:
      | filePath     | %tmp%/xlsx_variant_group_export/xlsx_variant_group_export.xlsx |
      | linesPerFile | 2                                                              |
    When I am on the "xlsx_variant_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_variant_group_export" job to finish
    Then I should see the secondary action "xlsx_variant_group_export_1.xlsx"
    And I should see the secondary action "xlsx_variant_group_export_2.xlsx"
    And exported xlsx file 1 of "xlsx_variant_group_export" should contain:
      | code     | type    | axis       | label-en_US | label-en_GB | label-fr_FR | label-de_DE |
      | tshirts  | variant | color,size | T-shirts    | T-shirts    | T-shirts    | T-Shirts    |
      | sweaters | variant | color,size | Sweaters    | Chandails   | Sweaters    | Pullovern   |
    And exported xlsx file 2 of "xlsx_variant_group_export" should contain:
      | code    | type    | axis                        | label-en_US | label-en_GB | label-fr_FR | label-de_DE |
      | jackets | variant | chest_size,color,waist_size | Jackets     | Jackets     | Vestes      | Jacken      |
