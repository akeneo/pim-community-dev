Feature: Import locales
  In order to setup my application
  As an administrator
  I need to be able to import locales

  Scenario: Successfully import new locale in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code
      fr_FR,1
      """
    When I import it via the job "csv_footwear_locale_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following locales:
      | code  | activated |
      | fr_FR | 1         |
