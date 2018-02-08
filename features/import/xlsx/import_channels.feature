Feature: Import channels
  In order to setup my application
  As an administrator
  I need to be able to import channels

  Scenario: Successfully import new channel in XLSX
    Given the "footwear" catalog configuration
    And the following XLSX file to import:
      """
      code;label-en_US;currencies;locales;tree
      site;Site;USD,EUR;de_DE,en_US,hy_AM;2014_collection
      """
    When I import it via the job "xlsx_footwear_channel_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following channels:
      | code | label-en_US | currencies | locales           | tree            | conversion_units |
      | site | Site        | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection |                  |

  Scenario: Successfully remove locales and currencies
    Given the "apparel" catalog configuration
    And the following jobs:
      | connector             | type   | alias               | code                | label               |
      | Akeneo XLSX Connector | import | xlsx_channel_import | xlsx_channel_import | XLSX channel import |
    And the following XLSX file to import:
      """
      code;label-en_US;currencies;locales;tree
      print;Print;USD;en_US;2015_collection
      """
    When I import it via the job "xlsx_channel_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following channels:
      | code  | label-en_US | currencies | locales | tree            | conversion_units |
      | print | Print       | USD        | en_US   | 2015_collection |                  |
