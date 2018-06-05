@javascript
Feature: Export currencies
  In order to be able to access and modify currencies data outside PIM
  As an administrator
  I need to be able to export currencies in xlsx format

  Scenario: Successfully export currencies in xlsx with headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_currency_export" configuration:
      | filePath | %tmp%/xlsx_footwear_currency_export/xlsx_footwear_currency_export.xlsx |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_currency_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_currency_export" job to finish
    Then exported xlsx file of "xlsx_footwear_currency_export" should contain:
      | code | activated |
      | USD  | 1         |
      | EUR  | 1         |
      | ADP  | 0         |
      | AED  | 0         |
      | AFA  | 0         |
      | AFN  | 0         |

  Scenario: Successfully export currencies in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_currency_export" configuration:
      | filePath   | %tmp%/xlsx_footwear_currency_export/xlsx_footwear_currency_export.xlsx |
      | withHeader | no                                                                     |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_currency_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_currency_export" job to finish
    Then exported xlsx file of "xlsx_footwear_currency_export" should contain:
      | USD | 1 |
      | EUR | 1 |
      | ADP | 0 |
      | AED | 0 |
      | AFA | 0 |
      | AFN | 0 |
