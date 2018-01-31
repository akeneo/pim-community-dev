@javascript
Feature: Export channels
  In order to be able to access and modify channels data outside PIM
  As an administrator
  I need to be able to export channels in xlsx format

  Scenario: Successfully export channels in xlsx with headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_channel_export" configuration:
      | filePath | %tmp%/xlsx_footwear_channel_export/xlsx_footwear_channel_export.xlsx |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_channel_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_channel_export" job to finish
    Then exported xlsx file of "xlsx_footwear_channel_export" should contain:
      | code   | label  | conversion_units | label-fr_FR | label-en_US | label-de_DE | currencies | locales     | tree            |
      | mobile | Mobile |                  | Mobile      | Mobile      | Mobile      | EUR        | en_US,fr_FR | 2014_collection |
      | tablet | Tablet |                  | Tablette    | Tablet      | Tablet      | USD,EUR    | en_US       | 2014_collection |

  Scenario: Successfully export channels in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_channel_export" configuration:
      | filePath   | %tmp%/xlsx_footwear_channel_export/xlsx_footwear_channel_export.xlsx |
      | withHeader | no                                                                   |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_channel_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_channel_export" job to finish
    Then exported xlsx file of "xlsx_footwear_channel_export" should contain:
      | mobile | Mobile | Mobil  | Mobile   | EUR     | en_US,fr_FR | 2014_collection |  |
      | tablet | Tablet | Tablet | Tablette | USD,EUR | en_US       | 2014_collection |  |
