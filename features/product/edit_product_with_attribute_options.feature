@javascript
Feature: Edit a product with attribute options
  In order to enrich the catalog
  As a regular user
  I need to be able edit a product with attribute options

  Background:
    Given the "footwear" catalog configuration
    And the following channels:
      | code      | label-en_US | currencies | locales | tree            |
      | ecommerce | Ecommerce   | EUR,USD    | en_US   | 2014_collection |
    And the following attributes:
      | code   | label-en_US | label-fr_FR | label-de_DE | type                     | group |
      | multi  | Multi       | Multi       | Multi       | pim_catalog_multiselect  | other |
      | simple | Simple      | Simple      | Simple      | pim_catalog_simpleselect | other |
    And the following products:
      | sku        | categories      |
      | rick_morty | 2014_collection |
    And I am logged in as "Peter"
    And I set the "English (United States), French (France), German (Germany)" locales to the "ecommerce" channel
    And the following CSV file to import:
      """
      code;label-fr_FR;label-de_DE;label-en_US;attribute;sort_order
      1;FR1;DE1;US1;multi;1
      2;FR2;DE2;US2;multi;2
      3;FR3;DE3;US3;multi;3
      4;FR4;DE4;US4;multi;4
      5;FR5;DE5;US5;multi;5
      6;FR6;DE6;US6;multi;6
      7;FR7;DE7;US7;multi;7
      8;FR8;DE8;US8;multi;8
      9;FR9;DE9;US9;multi;9
      10;FR10;DE10;US10;multi;10
      11;FR11;DE11;US11;multi;11
      12;FR12;DE12;US12;multi;12
      13;FR13;DE13;US13;multi;13
      14;FR14;DE14;US14;multi;14
      15;FR15;DE15;US15;multi;15
      16;FR16;DE16;US16;multi;16
      17;FR17;DE17;US17;multi;17
      18;FR18;DE18;US18;multi;18
      19;FR19;DE19;US19;multi;19
      20;FR20;DE20;US20;multi;20
      21;FR21;DE21;US21;multi;21
      22;FR22;DE22;US22;multi;22
      23;FR23;DE23;US23;multi;23
      24;FR24;DE24;US24;multi;24
      25;FR25;DE25;US25;multi;25
      26;FR26;DE26;US26;multi;26
      27;FR27;DE27;US27;multi;27
      28;FR28;DE28;US28;multi;28
      29;FR29;DE29;US29;multi;29
      30;FR30;DE30;US30;multi;30
      31;FR31;DE31;US31;multi;31
      32;FR32;DE32;US32;multi;32
      33;FR33;DE33;US33;multi;33
      34;FR34;DE34;US34;multi;34
      35;FR35;DE35;US35;multi;35
      hammer;MARTEAU;WURST;HAMMER;multi;36
      1;FR1;DE1;US1;simple;1
      2;FR2;DE2;US2;simple;2
      3;FR3;DE3;US3;simple;3
      4;FR4;DE4;US4;simple;4
      5;FR5;DE5;US5;simple;5
      6;FR6;DE6;US6;simple;6
      7;FR7;DE7;US7;simple;7
      8;FR8;DE8;US8;simple;8
      9;FR9;DE9;US9;simple;9
      10;FR10;DE10;US10;simple;10
      11;FR11;DE11;US11;simple;11
      12;FR12;DE12;US12;simple;12
      13;FR13;DE13;US13;simple;13
      14;FR14;DE14;US14;simple;14
      15;FR15;DE15;US15;simple;15
      16;FR16;DE16;US16;simple;16
      17;FR17;DE17;US17;simple;17
      18;FR18;DE18;US18;simple;18
      19;FR19;DE19;US19;simple;19
      20;FR20;DE20;US20;simple;20
      21;FR21;DE21;US21;simple;21
      22;FR22;DE22;US22;simple;22
      23;FR23;DE23;US23;simple;23
      24;FR24;DE24;US24;simple;24
      25;FR25;DE25;US25;simple;25
      26;FR26;DE26;US26;simple;26
      27;FR27;DE27;US27;simple;27
      28;FR28;DE28;US28;simple;28
      29;FR29;DE29;US29;simple;29
      30;FR30;DE30;US30;simple;30
      31;FR31;DE31;US31;simple;31
      32;FR32;DE32;US32;simple;32
      33;FR33;DE33;US33;simple;33
      34;FR34;DE34;US34;simple;34
      35;FR35;DE35;US35;simple;35
      hammer;MARTEAU;WURST;HAMMER;simple;36
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    And I logout

  @ce @jira https://akeneo.atlassian.net/browse/PIM-5993
  Scenario: I edit a multiselect attribute with localized options
    Given the following product values:
      | product    | attribute | value      |
      | rick_morty | multi     | hammer,2,3 |
    And I am logged in as "Julia"
    And I edit the "rick_morty" product
    And I switch the scope to "ecommerce"
    And I switch the locale to "de_DE"
    And I visit the "[other]" group
    Then I should see the text "DE2 DE3 WURST"
    When I switch the locale to "fr_FR"
    Then I should see the text "FR2 FR3 MARTEAU"
    When I change the "Multi" to "FR1, FR8"
    And I switch the locale to "de_DE"
    Then I should see the text "DE1 DE8"
    When I click on the field Multi
    Then I should see the text "DE8"
    And I should see the text "DE14"
    And I should see the text "DE19"
    And I should not see the text "DE30"

  @ce @jira https://akeneo.atlassian.net/browse/PIM-5993
  Scenario: I edit a simpleselect attribute with localized options
    Given the following product values:
      | product    | attribute | value  |
      | rick_morty | simple    | hammer |
    And I am logged in as "Julia"
    And I edit the "rick_morty" product
    And I switch the scope to "ecommerce"
    And I switch the locale to "de_DE"
    And I visit the "[other]" group
    Then I should see the text "WURST"
    When I switch the locale to "fr_FR"
    Then I should see the text "MARTEAU"
    When I change the "Simple" to "FR19"
    And I switch the locale to "de_DE"
    Then I should see the text "DE19"
