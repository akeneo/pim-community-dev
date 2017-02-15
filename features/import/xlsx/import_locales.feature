@javascript
Feature: Import locales
  In order to setup my application
  As an administrator
  I need to be able to import locales

  Scenario: Successfully import new locale in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code
      ru_MO
      """
    And the following job "xlsx_footwear_locale_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_locale_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_locale_import" job to finish
    Then there should be the following locales:
      | code  |
      | ru_MO |
