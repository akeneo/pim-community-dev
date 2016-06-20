@javascript
Feature: Import options
  In order to reuse the options
  As a product manager
  I need to be able to import options

  Scenario: Successfully import options in CSV
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
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
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
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    Then I should see "skipped 1"
    And I should see "code: This value should not be blank"

  Scenario: Skip options with unknown attribute code
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
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
    Then I should see "skipped 1"
    And I should see "Attribute \"unknown\" does not exist"

  @jira https://akeneo.atlassian.net/browse/PIM-3820
  Scenario: Import options with localizable label
    Given the "apparel" catalog configuration
    And the following attributes:
      | code | label | type         |
      | test | Test  | simpleselect |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      attribute;code;sort_order;label-fr_FR;label-en_US;label-de_DE;label-en_GB
      test;test_A04;3;04FR;04US;04DE;04GB
      test;test_A05;2;05FR;05US;05DE;05GB
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
    And I should see "05GB"
    And I should see "05GB"

  @jira https://akeneo.atlassian.net/browse/PIM-3820
  Scenario: Stop an import when a label is provided for a disabled language
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
    And I wait for the "option_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "Field \"label-fr_CA\" is provided, authorized fields are: \"attribute, code, sort_order, label-de_DE, label-en_GB, label-en_US, label-fr_FR\""

  Scenario: Import options with full numeric codes
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;1;Converse
      brand;22;TimberLand
      brand;30;Nike
      brand;04;Caterpillar
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    Then there should be the following options:
      | attribute | code | label-en_US |
      | brand     | 1    | Converse    |
      | brand     | 22   | TimberLand  |
      | brand     | 30   | Nike        |
      | brand     | 04   | Caterpillar |

  Scenario: Successfully import options in XLSX
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | brand | Brand | simpleselect |
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      attribute;code;label-en_US
      brand;Converse;Converse
      brand;TimberLand;TimberLand
      brand;Nike;Nike
      brand;Caterpillar;Caterpillar
      """
    And the following job "xlsx_footwear_option_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_option_import" job to finish
    Then there should be the following options:
      | attribute | code        | label-en_US |
      | brand     | Converse    | Converse    |
      | brand     | TimberLand  | TimberLand  |
      | brand     | Nike        | Nike        |
      | brand     | Caterpillar | Caterpillar |

  @jira https://akeneo.atlassian.net/browse/PIM-5682
    Scenario: Successfully update options without sort order
      Given the "footwear" catalog configuration
      And I am logged in as "Julia"
      Then there should be the following options with this order:
        | attribute          | code  | label-en_US |
        | weather_conditions | dry   | Dry         |
        | weather_conditions | wet   | Wet         |
        | weather_conditions | hot   | Hot         |
        | weather_conditions | cold  | Cold        |
        | weather_conditions | snowy | Snowy       |
      And the following XLSX file to import:
      """
      attribute;code;label-en_US
      weather_conditions;wet;Watery
      weather_conditions;dry;Rainy
      weather_conditions;cold;Very cold
      weather_conditions;snowy;Snow
      weather_conditions;hot;Sunny
      """
      And the following job "xlsx_footwear_option_import" configuration:
        | filePath | %file to import% |
      When I am on the "xlsx_footwear_option_import" import job page
      And I launch the import job
      And I wait for the "xlsx_footwear_option_import" job to finish
      Then there should be the following options with this order:
        | attribute          | code  | label-en_US |
        | weather_conditions | dry   | Rainy       |
        | weather_conditions | wet   | Watery      |
        | weather_conditions | hot   | Sunny       |
        | weather_conditions | cold  | Very cold   |
        | weather_conditions | snowy | Snow        |
