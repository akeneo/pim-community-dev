@javascript
Feature: Validate number attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for number attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code       | label-en_US | type   | scopable | unique | negative_allowed | decimals_allowed | number_min | number_max |
      | ref        | Reference   | number | no       | yes    | no               | no               |            |            |
      | sold       | Sold        | number | no       | no     | no               | no               |            |            |
      | available  | Available   | number | yes      | no     | no               | no               |            |            |
      | rating     | Rating      | number | no       | no     | no               | no               | 1          | 5          |
      | popularity | Popularity  | number | yes      | no     | no               | no               | 1          | 10         |
    And the following family:
      | code | label-en_US | attributes                                    |
      | baz  | Baz         | sku, ref, sold, available, rating, popularity |
    And the following products:
      | sku | family | popularity-mobile | popularity-ecommerce | rating |
      | foo | baz    | 4                 | 4                    | 1      |
      | bar | baz    | 4                 | 4                    | 2      |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the unique constraint of number attribute
    Given I change the Reference to "111"
    And I save the product
    When I am on the "bar" product page
    And I change the Reference to "111"
    And I save the product
    Then I should see validation tooltip "This value is already set on another product."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the negative allowed constraint of number attribute
    Given I change the Sold to "-1"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the negative allowed constraint of scopable number attribute
    Given I change the "ecommerce Available" to "-1"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the decimals allowed constraint of number attribute
    Given I change the Rating to "4.5"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the decimals allowed constraint of scopable number attribute
    Given I change the "ecommerce Popularity" to "9.5"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number min constraint of number attribute
    Given I change the Rating to "0"
    And I save the product
    Then I should see validation tooltip "This value should be 1.0000 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number min constraint of scopable number attribute
    Given I change the "ecommerce Popularity" to "0"
    And I save the product
    Then I should see validation tooltip "This value should be 1.0000 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number max constraint of number attribute
    Given I change the Rating to "6"
    And I save the product
    Then I should see validation tooltip "This value should be 5.0000 or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number max constraint of scopable number attribute
    Given I change the "ecommerce Popularity" to "11"
    And I save the product
    Then I should see validation tooltip "This value should be 10.0000 or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red
