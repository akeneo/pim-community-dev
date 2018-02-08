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
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
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
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;;Converse
      """
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "skipped 1"
    And I should see the text "code: This value should not be blank"

  Scenario: Skip options with unknown attribute code
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      unknown;option_code;Converse
      """
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "skipped 1"
    And I should see the text "Property \"attribute\" expects a valid attribute code. The attribute does not exist, \"unknown\" given."

  @jira https://akeneo.atlassian.net/browse/PIM-3820
  Scenario: Import options with localizable label
    Given the "apparel" catalog configuration
    And the following attributes:
      | code | label-en_US | type                     | group |
      | test | Test        | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;sort_order;label-fr_FR;label-en_US;label-de_DE;label-en_GB
      test;test_A04;3;04FR;04US;04DE;04GB
      test;test_A05;2;05FR;05US;05DE;05GB
      """
    When I import it via the job "option_import" as "Julia"
    And I wait for this job to finish
    And I am on the "Test" attribute page
    And I visit the "Options" tab
    And I should see the text "04FR"
    And I should see the text "04US"
    And I should see the text "04DE"
    And I should see the text "05FR"
    And I should see the text "05US"
    And I should see the text "05DE"
    And I should see the text "05GB"
    And I should see the text "05GB"

  @jira https://akeneo.atlassian.net/browse/PIM-3820
  Scenario: Stop an import when a label is provided for a disabled language
    Given the "apparel" catalog configuration
    And the following attributes:
      | code | label-en_US | type                     | group |
      | test | Test        | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;sort_order;label-fr_FR;label-fr_CA;label-en_US;label-de_DE
      test;test_A04;3;04FR;04CA;04US;04DE
      test;test_A05;2;05FR;05CA;05US;05DE
      """
    When I import it via the job "option_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text "Field \"label-fr_CA\" is provided, authorized fields are: \"attribute, code, sort_order, label-de_DE, label-en_GB, label-en_US, label-fr_FR\""

  Scenario: Import options with full numeric codes
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;1;Converse
      brand;22;TimberLand
      brand;30;Nike
      brand;04;Caterpillar
      """
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following options:
      | attribute | code | label-en_US |
      | brand     | 1    | Converse    |
      | brand     | 22   | TimberLand  |
      | brand     | 30   | Nike        |
      | brand     | 04   | Caterpillar |

  Scenario: Successfully import options in XLSX
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following XLSX file to import:
      """
      attribute;code;label-en_US
      brand;Converse;Converse
      brand;TimberLand;TimberLand
      brand;Nike;Nike
      brand;Caterpillar;Caterpillar
      """
    When I import it via the job "xlsx_footwear_option_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following options:
      | attribute | code        | label-en_US |
      | brand     | Converse    | Converse    |
      | brand     | TimberLand  | TimberLand  |
      | brand     | Nike        | Nike        |
      | brand     | Caterpillar | Caterpillar |

  @jira https://akeneo.atlassian.net/browse/PIM-5852
  Scenario: Does not overwrite sort order values when importing existing options
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | brand | Brand       | pim_catalog_simpleselect | other |
    And the following CSV file to import:
      """
      attribute;code;label-en_US;sort_order
      brand;Converse;Converse;1
      brand;TimberLand;TimberLand;2
      brand;Nike;Nike;3
      brand;Caterpillar;Caterpillar;4
      """
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
    And the following CSV file to import:
      """
      attribute;code;label-en_US
      brand;Caterpillar;Caterpillar
      brand;Nike;Nike
      brand;TimberLand;TimberLand
      brand;Converse;Converse
      """
    When I import it via the job "csv_footwear_option_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following options:
      | attribute | code        | label-en_US | sort_order |
      | brand     | Converse    | Converse    | 1          |
      | brand     | TimberLand  | TimberLand  | 2          |
      | brand     | Nike        | Nike        | 3          |
      | brand     | Caterpillar | Caterpillar | 4          |
