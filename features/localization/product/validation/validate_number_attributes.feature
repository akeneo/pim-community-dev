@javascript
Feature: Validate localized number attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for number attributes

  Background:
    Given the "default" catalog configuration
    And the following attribute groups:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | code       | label-fr_FR | type   | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group   |
      | ref        | Référence   | number | no       | yes    | no               | no               |            |            | other   |
      | sold       | Vendu       | number | no       | no     | no               | no               |            |            | other   |
      | available  | Disponible  | number | yes      | no     | no               | no               |            |            | other   |
      | rating     | Classement  | number | no       | no     | no               | no               | 1          | 5          | other   |
      | quality    | Qualité     | number | no       | no     | no               | yes              | 1          | 10         | other   |
      | popularity | Popularité  | number | yes      | no     | no               | no               | 1          | 10         | other   |
      | random     | Aléatoire   | number | yes      | no     | no               | no               |            |            | general |
    And the following family:
      | code | label-en_US | attributes                                                     | requirements-ecommerce | requirements-mobile |
      | baz  | Baz         | sku, ref, sold, available, rating, popularity, quality, random | sku                    | sku                 |
    And the following products:
      | sku | family | popularity-mobile | popularity-ecommerce | rating |
      | foo | baz    | 4                 | 4                    | 1      |
    And I am logged in as "Julien"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of number attribute
    Given I change the Vendu to "-1"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 0."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of number attribute
    Given I change the Classement to "4,5"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals allowed constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the "Popularité" to "9,5"
    And I save the product
    Then I should see validation tooltip "Cette valeur ne doit pas être un nombre décimal."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the number min constraint of number attribute
    Given I change the Classement to "0"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être supérieure ou égale à 1."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the type constraint of decimal attribute
    Given I change the "Qualité" to "qux"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être un nombre."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the type constraint of number attribute
    Given I change the Classement to "qux"
    And I save the product
    Then I should see validation tooltip "Cette valeur doit être un nombre."
    And there should be 1 error in the "[other]" tab

  Scenario: Validate the decimals separator constraint of number attribute
    Given I change the Classement to "4.5"
    And I save the product
    Then I should see validation tooltip "This type of value expects the use of , to separate decimals."
    And there should be 1 error in the "[other]" tab
