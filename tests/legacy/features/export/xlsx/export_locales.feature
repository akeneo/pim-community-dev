@javascript
Feature: Export locales
  In order to be able to access and modify locales data outside PIM
  As an administrator
  I need to be able to export locales in xlsx format

  Scenario: Successfully export locales in xlsx with headers:
    Given a "default" catalog configuration
    And the following jobs:
      | connector             | type   | alias              | code               | label         |
      | Akeneo XLSX Connector | export | xlsx_locale_export | xlsx_locale_export | locale export |
    And I am logged in as "Julia"
    When I am on the "xlsx_locale_export" export job page
    And I launch the export job
    And I wait for the "xlsx_locale_export" job to finish
    Then exported xlsx file of "xlsx_locale_export" should contain:
      | code       | activated |
      | ar_AE      | 0         |
      | az_Cyrl_AZ | 0         |
      | de_DE      | 0         |
      | en_GB      | 0         |
      | en_US      | 1         |
      | es_ES      | 0         |
      | fr_FR      | 1         |
      | as_IN      | 0         |

  Scenario: Successfully export locales in xlsx without headers:
    Given a "default" catalog configuration
    And the following jobs:
      | connector             | type   | alias              | code               | label         |
      | Akeneo XLSX Connector | export | xlsx_locale_export | xlsx_locale_export | locale export |
    And the following job "xlsx_locale_export" configuration:
      | withHeader | no |
    And I am logged in as "Julia"
    When I am on the "xlsx_locale_export" export job page
    And I launch the export job
    And I wait for the "xlsx_locale_export" job to finish
    Then exported xlsx file of "xlsx_locale_export" should contain:
      | ar_AE      | 0 |
      | az_Cyrl_AZ | 0 |
      | de_DE      | 0 |
      | en_GB      | 0 |
      | en_US      | 1 |
      | es_ES      | 0 |
      | fr_FR      | 1 |
      | as_IN      | 0 |
