@javascript
Feature: Import currencies
  In order to setup my application
  As an administrator
  I need to be able to import currencies

  Scenario: Successfully import new currency in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;activated
      AMD;1
      """
    And the following job "xlsx_footwear_currency_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_currency_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_currency_import" job to finish
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |
