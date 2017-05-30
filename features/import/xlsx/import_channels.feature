@javascript
Feature: Import channels
  In order to setup my application
  As an administrator
  I need to be able to import channels

  Scenario: Successfully import new channel in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;label-en_US;currencies;locales;tree
      site;Site;USD,EUR;de_DE,en_US,hy_AM;2014_collection
      """
    And the following job "xlsx_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code | label-en_US | currencies | locales           | tree            | conversion_units |
      | site | Site        | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection |                  |

  Scenario: Successfully remove locales and currencies
    Given the "apparel" catalog configuration
    And the following jobs:
      | connector             | type   | alias               | code                | label               |
      | Akeneo XLSX Connector | import | xlsx_channel_import | xlsx_channel_import | XLSX channel import |
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;label-en_US;currencies;locales;tree
      print;Print;USD;en_US;2015_collection
      """
    And the following job "xlsx_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_channel_import" import job page
    And I launch the import job
    And I wait for the "xlsx_channel_import" job to finish
    Then there should be the following channels:
      | code  | label-en_US | currencies | locales | tree            | conversion_units |
      | print | Print       | USD        | en_US   | 2015_collection |                  |
