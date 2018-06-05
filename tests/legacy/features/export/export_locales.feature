@javascript
Feature: Export locales
  In order to be able to access and modify locales data outside PIM
  As an administrator
  I need to be able to export locales

  Scenario: Successfully export locales
    Given a "default" catalog configuration
    And the following jobs:
      | connector            | type   | alias             | code              | label         |
      | Akeneo CSV Connector | export | csv_locale_export | csv_locale_export | locale export |
    And I am logged in as "Julia"
    And I am on the "csv_locale_export" export job page
    When I launch the export job
    And I wait for the "csv_locale_export" job to finish
    Then I should see the text "Read 8"
    And I should see the text "Written 8"
    And exported file of "csv_locale_export" should contain:
    """
    code;activated
    ar_AE;0
    az_Cyrl_AZ;0
    de_DE;0
    en_GB;0
    en_US;1
    es_ES;0
    fr_FR;1
    as_IN;0
    """
