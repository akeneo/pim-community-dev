@javascript
Feature: Validate localized price attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for price attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code    | label-fr_FR | type                         | scopable | decimals_allowed | number_min | number_max | group |
      | cost    | Coût        | pim_catalog_price_collection | 0        | 0                |            |            | other |
      | price   | Prix        | pim_catalog_price_collection | 1        | 0                |            |            | other |
      | tax     | Taxe        | pim_catalog_price_collection | 0        | 1                | 10         | 100        | other |
      | customs | Douane      | pim_catalog_price_collection | 1        | 1                | 10         | 100        | other |
    And the following family:
      | code | label-en_US | attributes                 |
      | baz  | Baz         | sku,cost,price,tax,customs |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Julien"
    And I am on the "foo" product page

  Scenario: Validate the decimals allowed constraint of price attribute
    Given I change the "Coût" to "2,7 USD"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of scopable price attribute
    Given I change the "Prix" to "4,9 EUR"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number min constraint of price attribute
    Given I change the "Taxe" to "5,5 USD"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 10."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number min constraint of scopable price attribute
    Given I change the "Douane" to "9,9 EUR"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 10."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number max constraint of price attribute
    Given I change the "Taxe" to "110 USD"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être inférieure ou égale à 100."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number max constraint of scopable price attribute
    Given I change the "Douane" to "222,2 EUR"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être inférieure ou égale à 100."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the type constraint of price attribute
    Given I change the "Taxe" to "bar USD"
    And I change the "Taxe" to "qux EUR"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être un nombre."
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 10."
    And there should be 2 error in the "[other]" tab

  Scenario: Validate the decimal separator constraint of price attribute
    Given I change the "Taxe" to "50.50 EUR"
    And I save the product
    Then I should see validation tooltip "Ce type de valeur attend une virgule (,) comme séparateur de décimales."
    And there should be 1 error in the "[other]" tab
