@javascript
Feature: Import locales
  In order to setup my application
  As an administrator
  I need to be able to import locales

  Scenario: Successfully import new locale in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code
      fr_FR,1
      """
    And the following job "csv_footwear_locale_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_locale_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_locale_import" job to finish
    Then there should be the following locales:
      | code  | activated |
      | fr_FR | 1         |
