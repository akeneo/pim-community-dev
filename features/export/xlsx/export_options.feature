@javascript
Feature: Export options in XSLX
  In order to be able to access and modify options data outside PIM
  As a product manager
  I need to be able to export options in XLSX

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export options in XLSX
    Given the following job "xlsx_footwear_option_export" configuration:
      | filePath | %tmp%/option_export/option_export.xlsx |
    And I am on the "xlsx_footwear_option_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_option_export" job to finish
    Then exported xlsx file of "xlsx_footwear_option_export" should contain:
      | attribute          | code        | sort_order | label-en_US |
      | manufacturer       | Converse    | 1          | Converse    |
      | manufacturer       | TimberLand  | 2          | TimberLand  |
      | manufacturer       | Nike        | 3          | Nike        |
      | manufacturer       | Caterpillar | 4          | Caterpillar |
      | weather_conditions | dry         | 1          | Dry         |
      | weather_conditions | wet         | 2          | Wet         |
      | weather_conditions | hot         | 3          | Hot         |
      | weather_conditions | cold        | 4          | Cold        |
      | weather_conditions | snowy       | 5          | Snowy       |
      | rating             | 1           | 1          | 1 star      |
      | rating             | 2           | 2          | 2 stars     |
      | rating             | 3           | 3          | 3 stars     |
      | rating             | 4           | 4          | 4 stars     |
      | rating             | 5           | 5          | 5 stars     |
      | size               | 35          | 1          | 35          |
      | size               | 36          | 2          | 36          |
      | size               | 37          | 3          | 37          |
      | size               | 38          | 4          | 38          |
      | size               | 39          | 5          | 39          |
      | size               | 40          | 6          | 40          |
      | size               | 41          | 7          | 41          |
      | size               | 42          | 8          | 42          |
      | size               | 43          | 9          | 43          |
      | size               | 44          | 10         | 44          |
      | size               | 45          | 11         | 45          |
      | size               | 46          | 12         | 46          |
      | size               | 60          | 13         | 60          |
      | color              | white       | 1          | White       |
      | color              | black       | 2          | Black       |
      | color              | blue        | 3          | Blue        |
      | color              | maroon      | 4          | Maroon      |
      | color              | saddle      | 5          | Saddle      |
      | color              | greem       | 6          | Greem       |
      | color              | red         | 7          | Red         |
      | color              | charcoal    | 8          | Charcoal    |
      | lace_color         | laces_black | 1          | Black       |
      | lace_color         | laces_brown | 2          | Brown       |
      | lace_color         | laces_white | 3          | White       |

  Scenario: Successfully export groups in XLSX into several files
    Given the following job "xlsx_footwear_option_export" configuration:
      | filePath     | %tmp%/xlsx_footwear_option_export/xlsx_footwear_option_export.xlsx |
      | linesPerFile | 5                                                                  |
    When I am on the "xlsx_footwear_option_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_option_export" job to finish
    Then I should see the secondary action "xlsx_footwear_option_export_1.xlsx"
    And I should see the secondary action "xlsx_footwear_option_export_2.xlsx"
    And exported xlsx file 1 of "xlsx_footwear_option_export" should contain:
      | attribute          | code        | sort_order | label-en_US |
      | manufacturer       | Converse    | 1          | Converse    |
      | manufacturer       | TimberLand  | 2          | TimberLand  |
      | manufacturer       | Nike        | 3          | Nike        |
      | manufacturer       | Caterpillar | 4          | Caterpillar |
      | weather_conditions | dry         | 1          | Dry         |
    And exported xlsx file 2 of "xlsx_footwear_option_export" should contain:
      | attribute          | code  | sort_order | label-en_US |
      | weather_conditions | wet   | 2          | Wet         |
      | weather_conditions | hot   | 3          | Hot         |
      | weather_conditions | cold  | 4          | Cold        |
      | weather_conditions | snowy | 5          | Snowy       |
      | rating             | 1     | 1          | 1 star      |
