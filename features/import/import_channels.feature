@javascript
Feature: Import channels
  In order to setup my application
  As an administrator
  I need to be able to import channels

  Scenario: Successfully import new channel in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label;currencies;locales;tree;conversion_units
      site;Site;USD,EUR;de_DE,en_US,hy_AM;2014_collection;"weight: GRAM, maximum_scan_size: KILOMETER, display_diagonal: DEKAMETER, viewing_area: DEKAMETER"
      """
    And the following job "csv_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code | label | currencies | locales           | tree            | conversion_units                                                                                 |
      | site | Site  | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection | weight: GRAM, maximum_scan_size: KILOMETER, display_diagonal: DEKAMETER, viewing_area: DEKAMETER |

  Scenario: Successfully update existing channel and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label;currencies;locales;tree
      site;Site;USD,EUR;de_DE,en_US,fr_FR;2014_collection
      mobile;Mobile app;EUR,USD;en_US,fr_FR;2014_collection
      """
    And the following job "csv_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code   | label      | currencies | locales           | tree            | conversion_units |
      | site   | Site       | EUR,USD    | de_DE,en_US,fr_FR | 2014_collection |                  |
      | mobile | Mobile app | EUR,USD    | en_US,fr_FR       | 2014_collection |                  |

  Scenario: Successfully remove locales and currencies
    Given the "apparel" catalog configuration
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | import | csv_channel_import | csv_channel_import | CSV channel import |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label;currencies;locales;tree
      print;Print;USD;en_US;2015_collection
      """
    And the following job "csv_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_channel_import" import job page
    And I launch the import job
    And I wait for the "csv_channel_import" job to finish
    Then there should be the following channels:
      | code  | label | currencies | locales | tree            | conversion_units |
      | print | Print | USD        | en_US   | 2015_collection |                  |
