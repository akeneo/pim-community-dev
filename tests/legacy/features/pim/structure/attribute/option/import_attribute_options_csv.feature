Feature: Import options
  In order to reuse the options
  As a product manager
  I need to be able to import options

  Scenario: Successfully import options in CSV
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;Converse;Converse
      brand;TimberLand;TimberLand
      brand;Nike;Nike
      brand;Caterpillar;Caterpillar
      """
    When the attribute options are imported via the job csv_footwear_option_import
    Then there should be the following options:
      | attribute | code        | label-en_US |
      | brand     | Converse    | Converse    |
      | brand     | TimberLand  | TimberLand  |
      | brand     | Nike        | Nike        |
      | brand     | Caterpillar | Caterpillar |

  @javascript
  Scenario: Skip options with unknown attribute code
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      unknown;option_code;Converse
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "Property \"attribute\" expects a valid attribute code. The attribute does not exist, \"unknown\" given."
