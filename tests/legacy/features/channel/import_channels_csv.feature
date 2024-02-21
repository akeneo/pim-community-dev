Feature: Import channels
  In order to setup my application
  As an administrator
  I need to be able to import channels

  Scenario: Successfully import new channel in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree;conversion_units
      site;Site;USD,EUR;de_DE,en_US,hy_AM;2014_collection;"weight: GRAM, length: MILLIMETER, volume: LITER"
      """
    When the channels are imported via the job csv_footwear_channel_import
    Then there should be the following channels:
      | code | label-en_US | currencies | locales           | tree            | conversion_units                           |
      | site | Site        | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection | weight:GRAM,length:MILLIMETER,volume:LITER |

  Scenario: Successfully remove locales and currencies
    Given the "apparel" catalog configuration
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | import | csv_channel_import | csv_channel_import | CSV channel import |
    And the following CSV file to import:
      """
      code;label-en_US;currencies;locales;tree
      print;Print;USD;en_US;2015_collection
      """
    When the channels are imported via the job csv_channel_import
    Then there should be the following channels:
      | code  | label-en_US | currencies | locales | tree            | conversion_units |
      | print | Print       | USD        | en_US   | 2015_collection |                  |
