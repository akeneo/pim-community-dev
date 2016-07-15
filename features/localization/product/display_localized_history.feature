@javascript
Feature: Display the localized product history
  In order to have complete localized UI
  As a product manager
  I need to have show localized values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code   | label  | label-fr_FR | type   | decimals_allowed | negative_allowed | default_metric_unit | metric_family | group |
      | number | Number | Nombre      | number | true             | false            |                     |               | other |
      | metric | Metric | Metrique    | metric | true             | true             | GRAM                | Weight        | other |
      | price  | Price  | Prix        | prices | true             | false            |                     |               | other |
    And I am logged in as "admin"
    And the following CSV file to import:
      """
      sku;price-EUR;price-USD;metric;metric-unit;number
      boots;20.80;25.35;12.1234;GRAM;98.7654
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I logout

  Scenario: Display french-format product history numbers
    Given I am logged in as "Julien"
    And I am on the products page
    And I click on the "boots" row
    And I wait to be on the "boots" product page
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property    | value     |
      | 1       | SKU         | boots     |
      | 1       | Metric      | 12,1234   |
      | 1       | Metric unit | Gramme    |
      | 1       | Number      | 98,7654   |
      | 1       | Price EUR   | 20,80 €   |
      | 1       | Price USD   | 25,35 $US |

  Scenario: Display english-format product history numbers
    Given I am logged in as "Julia"
    And I am on the products page
    And I click on the "boots" row
    And I wait to be on the "boots" product page
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property    | value   |
      | 1       | SKU         | boots   |
      | 1       | Metric      | 12.1234 |
      | 1       | Metric unit | Gram    |
      | 1       | Number      | 98.7654 |
      | 1       | Price EUR   | €20.80  |
      | 1       | Price USD   | $25.35  |
