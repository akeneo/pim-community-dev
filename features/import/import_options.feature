@javascript
Feature: Import options
  In order to reuse the options
  As Julia
  I need to be able to import options

  Scenario: Successfully import options
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
    And I am logged in as "Julia"
    And the following file to import:
    """
    attribute;code;default;label-en_US
    brand;Converse;0;Converse
    brand;TimberLand;0;TimberLand
    brand;Nike;0;Nike
    brand;Caterpillar;1;Caterpillar
    """
    And the following job "footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_option_import" import job page
    And I launch the import job
    And I wait for the "footwear_option_import" job to finish
    Then there should be the following options:
      | attribute | code        | default | label-en_US |
      | brand     | Converse    | 0       | Converse    |
      | brand     | TimberLand  | 0       | TimberLand  |
      | brand     | Nike        | 0       | Nike        |
      | brand     | Caterpillar | 1       | Caterpillar |

