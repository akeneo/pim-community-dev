Feature: Setup currencies via import
  In order to setup my application
  As an administrator
  I need to be able to import currencies

  Scenario: Successfully update existing currency and add a new one
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;activated
      AMD;1
      ARM;0
      """
    When the currencies are imported via the job csv_footwear_currency_import
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |
      | ARM  | 0         |
