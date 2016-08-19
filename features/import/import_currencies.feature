@javascript
Feature: Import currencies
  In order to setup my application
  As an administrator
  I need to be able to import currencies

  Scenario: Successfully import new currency in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;activated
      AMD;1
      """
    And the following job "csv_footwear_currency_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_currency_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_currency_import" job to finish
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |

  Scenario: Successfully update existing currency and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;activated
      AMD;1
      ARM;0
      """
    And the following job "csv_footwear_currency_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_currency_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_currency_import" job to finish
    Then there should be the following currencies:
      | code | activated |
      | AMD  | 1         |
      | ARM  | 0         |
