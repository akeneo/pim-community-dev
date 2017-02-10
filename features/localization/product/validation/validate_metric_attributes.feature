@javascript
Feature: Validate localized metric attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for metric attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | label-fr_FR | type               | scopable | localizable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | area   | Zone        | pim_catalog_metric | 0        | 1           | Area          | HECTARE             | 0                | 0                |            |            | other |
      | length | Taille      | pim_catalog_metric | 1        | 1           | Length        | METER               | 0                | 0                |            |            | other |
      | power  | Puissance   | pim_catalog_metric | 0        | 1           | Power         | WATT                | 1                | 1                | -200       | -100       | other |
      | speed  | Vitesse     | pim_catalog_metric | 1        | 1           | Speed         | YARD_PER_HOUR       | 1                | 1                | 5.50       | 100        | other |
    And the following family:
      | code | label-en_US | attributes                  |
      | baz  | Baz         | sku,area,length,power,speed |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Julien"
    And I am on the "foo" product page

  Scenario: Validate the decimals allowed constraint of metric attribute
    Given I change the Zone to "2,7 Hectare"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Taille to "4,9 Mètre"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number min constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Vitesse to "-7,5 Yard par heure"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 5.5."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number max constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Vitesse to "111,1 Yard par heure"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être inférieure ou égale à 100."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimal separator constraint of metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Vitesse to "50.1 Yard par heure"
    And I save the product
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."
    And there should be 1 error in the "[other]" tab
