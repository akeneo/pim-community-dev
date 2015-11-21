@javascript
Feature: Display the localized product history
  In order to have complete localized UI
  As a product manager
  I need to have show localized values

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code   | label  | label-fr_FR | type   | decimals_allowed | negative_allowed | default_metric_unit | metric_family | group |
      | number | Number | Nombre      | number | true             | false            |                     |               | other |
      | metric | Metric | Metrique    | metric | true             | true             | GRAM                | Weight        | other |
      | price  | Price  | Prix        | prices | true             | false            |                     |               | other |
    And the following products:
      | sku      | price                | metric       | number  |
      | boots    | 20.80 EUR, 25.35 USD | 12.1234 GRAM | 98.7654 |
    And the history of the product "boots" has been built

  Scenario: Display french-format product history numbers
    Given I am logged in as "Julien"
    And I edit the "boots" product
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property | value   |
      | 1       | SKU      | boots   |
      | 1       | Metrique | 12,1234 |
      | 1       | Nombre   | 98,7654 |
      | 1       | Prix EUR | 20,80   |
      | 1       | Prix USD | 25,35   |

  Scenario: Display english-format product history numbers
    Given I am logged in as "Julia"
    And I edit the "boots" product
    When I open the history
    Then there should be 1 update
    And I should see history:
      | version | property  | value   |
      | 1       | SKU       | boots   |
      | 1       | Metric    | 12.1234 |
      | 1       | Number    | 98.7654 |
      | 1       | Price EUR | 20.80   |
      | 1       | Price USD | 25.35   |
