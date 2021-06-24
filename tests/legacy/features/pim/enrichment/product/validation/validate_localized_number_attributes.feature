@javascript
Feature: Validate localized number attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for number attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code       | label-fr_FR | type               | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group |
      | sold       | Vendu       | pim_catalog_number | 0        | 0      | 0                | 0                |            |            | other |
      | rating     | Classement  | pim_catalog_number | 0        | 0      | 0                | 1                | 1          | 5          | other |
      | quality    | Qualité     | pim_catalog_number | 0        | 0      | 0                | 1                | 1          | 10         | other |
      | popularity | Popularité  | pim_catalog_number | 1        | 0      | 0                | 0                | 1          | 10         | other |
    And the following family:
      | code | label-en_US | attributes                         | requirements-ecommerce | requirements-mobile |
      | baz  | Baz         | sku,sold,rating,popularity,quality | sku                    | sku                 |
    And the following products:
      | sku | family | popularity-mobile | popularity-ecommerce | rating |
      | foo | baz    | 4                 | 4                    | 1      |
    And I am logged in as "Julien"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of number attribute
    Given I change the Vendu to "-1"
    And I save the product
    Then I should see validation tooltip "L'attribut sold requiert une valeur égale ou supérieure à 0"
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of number attribute
    Given I change the Vendu to "4,5"
    And I save the product
    Then I should see validation tooltip "L'attribut sold requiert un nombre entier et la valeur 4.5 soumise ne l'est pas."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the "Popularité" to "9,5"
    And I save the product
    Then I should see validation tooltip "L'attribut popularity requiert un nombre entier et la valeur 9.5 soumise ne l'est pas."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number min constraint of number attribute
    Given I change the Classement to "0"
    And I save the product
    Then I should see validation tooltip "L'attribut rating requiert une valeur égale ou supérieure à 1"
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the type constraint of decimal attribute
    Given I change the "Qualité" to "qux"
    And I save the product
    Then I should see validation tooltip "L'attribut quality requiert une valeur numérique et la valeur qux soumise ne l'est pas."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the type constraint of number attribute
    Given I change the Classement to "qux"
    And I save the product
    Then I should see validation tooltip "L'attribut rating requiert une valeur numérique et la valeur qux soumise ne l'est pas."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals separator constraint of number attribute
    Given I change the Classement to "4.5"
    And I save the product
    Then I should see validation tooltip "Ce type de valeur attend une virgule (,) comme séparateur de décimales."
    And there should be 1 error in the "[other]" tab
