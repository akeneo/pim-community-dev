Feature: Import locales
  In order to setup my application
  As an administrator
  I need to be able to import locales

  Scenario: Successfully import new locale in XLSX
    Given the "footwear" catalog configuration
    And the following XLSX file to import:
      """
      code
      ru_MO
      """
    When I import it via the job "xlsx_footwear_locale_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following locales:
      | code  |
      | ru_MO |
