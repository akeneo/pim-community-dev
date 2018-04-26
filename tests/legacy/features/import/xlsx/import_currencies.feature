Feature: Import currencies
  In order to setup my application
  As an administrator
  I need to be able to import currencies

  Scenario: Successfully import new currency in XLSX
    Given the "footwear" catalog configuration
    And the following XLSX file to import:
      """
      code;activated
      AMD;1
      """
    When the currencies are imported via the job xlsx_footwear_currency_import
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |
