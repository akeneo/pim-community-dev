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
    When I import it via the job "xlsx_footwear_currency_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |
