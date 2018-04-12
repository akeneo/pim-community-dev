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
    When the locales are imported via the job csv_footwear_locale_import
    Then there should be the following locales:
      | code  | activated |
      | fr_FR | 1         |
