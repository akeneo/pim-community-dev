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
      | code   | label  | color | currencies | locales | tree            |
      | mobile | Mobile |       | EUR        | en_US   | 2014_collection |
      | tablet | Tablet |       | EUR,USD    | en_US   | 2014_collection |

  Scenario: Successfully export channels in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_channel_export" configuration:
      | filePath    | %tmp%/xlsx_footwear_channel_export/xlsx_footwear_channel_export.xlsx |
      | withHeader  | no                                                                   |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_channel_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_channel_export" job to finish
    Then exported xlsx file of "xlsx_footwear_channel_export" should contain:
      | tablet | Tablet | EUR,USD | en_US | 2014_collection | |
      | mobile | Mobile | EUR     | en_US | 2014_collection | |
