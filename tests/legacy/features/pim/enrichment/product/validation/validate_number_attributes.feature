@javascript
Feature: Validate number attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for number attributes

  Background:
    Given the "default" catalog configuration
    And the following attribute groups:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | code       | label-en_US | type               | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max | group   |
      | ref        | Reference   | pim_catalog_number | 0        | 1      | 0                | 0                |            |            | other   |
      | sold       | Sold        | pim_catalog_number | 0        | 0      | 0                | 0                |            |            | other   |
      | available  | Available   | pim_catalog_number | 1        | 0      | 0                | 0                |            |            | other   |
      | rating     | Rating      | pim_catalog_number | 0        | 0      | 0                | 0                | 1          | 5          | other   |
      | quality    | Quality     | pim_catalog_number | 0        | 0      | 0                | 1                | 1          | 10         | other   |
      | popularity | Popularity  | pim_catalog_number | 1        | 0      | 0                | 0                | 1          | 10         | other   |
      | random     | Random      | pim_catalog_number | 1        | 0      | 0                | 0                |            |            | general |
    And the following family:
      | code | label-en_US | attributes                                              | requirements-ecommerce | requirements-mobile |
      | baz  | Baz         | sku,ref,sold,available,rating,popularity,quality,random | sku                    | sku                 |
    And the following products:
      | sku | family | popularity-mobile | popularity-ecommerce | rating |
      | foo | baz    | 4                 | 4                    | 1      |
      | bar | baz    | 4                 | 4                    | 2      |
    And I am logged in as "Mary"
    And I am on the "foo" product page
    And I visit the "Other" group

  Scenario: Validate the unique constraint of number attribute
    Given I change the Reference to "111"
    And I save the product
    When I am on the "bar" product page
    And I change the Reference to "111"
    And I save the product
    Then I should see validation tooltip "The value 111 is already set on another product for the unique attribute ref"
    And there should be 1 error in the "Other" tab

  @ce
  Scenario: Validate the negative allowed constraint of number attribute
    Given I change the Sold to "-1"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the negative allowed constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the Available to "-1"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of number attribute
    Given I change the Rating to "4.5"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the Popularity to "9.5"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of number attribute
    Given I change the Rating to "0"
    And I save the product
    Then I should see validation tooltip "This value should be between 1 and 5."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the Popularity to "0"
    And I save the product
    Then I should see validation tooltip "This value should be between 1 and 10."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of number attribute
    Given I change the Rating to "6"
    And I save the product
    Then I should see validation tooltip "This value should be between 1 and 5."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of scopable number attribute
    Given I switch the scope to "ecommerce"
    And I change the Popularity to "11"
    And I save the product
    Then I should see validation tooltip "This value should be between 1 and 10."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the type constraint of decimal attribute
    Given I change the Quality to "qux"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the type constraint of number attribute
    Given I change the Rating to "qux"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    And there should be 1 error in the "Other" tab
