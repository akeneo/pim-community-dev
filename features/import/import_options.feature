@javascript
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

  @jira https://akeneo.atlassian.net/browse/PIM-3820
  Scenario: Import options with localizable label
    Given the "apparel" catalog configuration
    And the following attributes:
      | code | label | type         |
      | test | Test  | simpleselect |
    And I am logged in as "Julia"
    And the following CSV file to import:
    """
    attribute;code;sort_order;label-fr_FR;label-fr_CA;label-en_US;label-de_DE
    test;test_A04;3;04FR;04CA;04US;04DE
    test;test_A05;2;05FR;05CA;05US;05DE
    """
    And the following job "option_import" configuration:
      | filePath | %file to import% |
    When I am on the "option_import" import job page
    And I launch the import job
    Then I wait for the "option_import" job to finish
    And I edit the "Test" attribute
    And I visit the "Values" tab
    Then I should see the "Options" section
    And I should see "04FR"
    And I should see "04US"
    And I should see "04DE"
    And I should see "05FR"
    And I should see "05US"
    And I should see "05DE"
    And I should not see "04CA"
    And I should not see "04CA"
    And I should not see "04CA"
