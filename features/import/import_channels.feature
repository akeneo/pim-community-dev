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
      code;label;color;currencies;locales;tree
      site;Site;blue;USD,EUR;de_DE,en_US,hy_AM;2014_collection
      """
    And the following job "csv_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code | label | color | currencies | locales           | tree            |
      | site | Site  | blue  | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection |

  Scenario: Successfully update existing channel and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label;color;currencies;locales;tree
      site;Site;blue;USD,EUR;de_DE,en_US,fr_FR;2014_collection
      mobile;Mobile app;red;EUR,USD;en_US,fr_FR;2014_collection
      """
    And the following job "csv_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code   | label      | color | currencies | locales           | tree            |
      | site   | Site       | blue  | EUR,USD    | de_DE,en_US,fr_FR | 2014_collection |
      | mobile | Mobile app | red   | EUR,USD    | en_US,fr_FR       | 2014_collection |

  Scenario: Successfully import new channel in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;label;color;currencies;locales;tree
      site;Site;blue;USD,EUR;de_DE,en_US,hy_AM;2014_collection
      """
    And the following job "xlsx_footwear_channel_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_channel_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_channel_import" job to finish
    Then there should be the following channels:
      | code | label | color | currencies | locales           | tree            |
      | site | Site  | blue  | EUR,USD    | de_DE,en_US,hy_AM | 2014_collection |
