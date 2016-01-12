@deprecated @javascript
Feature: Import options
  In order to reuse the options
  As a product manager
  I need to be able to import options

  Scenario: Successfully import options
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;Converse;Converse
      brand;TimberLand;TimberLand
      brand;Nike;Nike
      brand;Caterpillar;Caterpillar
      """
    And the following job "footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_option_import" import job page
    And I launch the import job
    And I wait for the "footwear_option_import" job to finish
    Then there should be the following options:
      | attribute | code        | label-en_US |
      | brand     | Converse    | Converse    |
      | brand     | TimberLand  | TimberLand  |
      | brand     | Nike        | Nike        |
      | brand     | Caterpillar | Caterpillar |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip options with empty code
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;;Converse
      """
    And the following job "footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_option_import" import job page
    And I launch the import job
    And I wait for the "footwear_option_import" job to finish
    Then I should see "skipped 1"
    And I should see "code: This value should not be blank"
