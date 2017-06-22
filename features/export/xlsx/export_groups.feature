@javascript
Feature: Export groups in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export groups in XLSX

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export groups in xlsx with headers
    Given the following job "xlsx_group_export" configuration:
      | filePath | %tmp%/xlsx_group_export/xlsx_group_export.xlsx |
    When I am on the "xlsx_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_group_export" job to finish
    Then exported xlsx file of "xlsx_group_export" should contain:
      | code       | type       | label-en_US | label-en_GB | label-fr_FR      | label-de_DE   |
      | similar    | similar    | Similar     | Similar     | Similaire        | Ähnlich       |
      | related    | related    | Related     | Related     | Connexe          | Verbunden     |
      | cross_sell | cross_sell | Cross sell  | Cross sell  | Vente croisée    | Cross-Selling |
      | upsell     | upsell     | Upsell      | Upsell      | Vente incitative | Upsell        |
      | substitute | substitute | Substitute  | Substitute  | Substitut        | Ersatz        |

  Scenario: Successfully export groups in xlsx without headers
    Given the following job "xlsx_group_export" configuration:
      | filePath   | %tmp%/xlsx_group_export/xlsx_group_export.xlsx |
      | withHeader | no                                             |
    When I am on the "xlsx_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_group_export" job to finish
    Then exported xlsx file of "xlsx_group_export" should contain:
      | similar    | similar    | Similar    | Similar    | Similaire        | Ähnlich       |
      | related    | related    | Related    | Related    | Connexe          | Verbunden     |
      | cross_sell | cross_sell | Cross sell | Cross sell | Vente croisée    | Cross-Selling |
      | upsell     | upsell     | Upsell     | Upsell     | Vente incitative | Upsell        |
      | substitute | substitute | Substitute | Substitute | Substitut        | Ersatz        |

  Scenario: Successfully export groups in XLSX into several files
    Given the following job "xlsx_group_export" configuration:
      | filePath     | %tmp%/xlsx_group_export/xlsx_group_export.xlsx |
      | linesPerFile | 3                                              |
    When I am on the "xlsx_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_group_export" job to finish
    Then I should see the secondary action "xlsx_group_export_1.xlsx"
    And I should see the secondary action "xlsx_group_export_2.xlsx"
    And exported xlsx file 1 of "xlsx_group_export" should contain:
      | code       | type       | label-en_US | label-en_GB | label-fr_FR   | label-de_DE   |
      | similar    | similar    | Similar     | Similar     | Similaire     | Ähnlich       |
      | related    | related    | Related     | Related     | Connexe       | Verbunden     |
      | cross_sell | cross_sell | Cross sell  | Cross sell  | Vente croisée | Cross-Selling |
    And exported xlsx file 2 of "xlsx_group_export" should contain:
      | code       | type       | label-en_US | label-en_GB | label-fr_FR      | label-de_DE |
      | upsell     | upsell     | Upsell      | Upsell      | Vente incitative | Upsell      |
      | substitute | substitute | Substitute  | Substitute  | Substitut        | Ersatz      |
