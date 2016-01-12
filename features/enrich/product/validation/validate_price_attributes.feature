@javascript
Feature: Validate price attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for price attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code    | label-en_US | type   | scopable | negative_allowed | decimals_allowed | number_min | number_max |
      | cost    | Cost        | prices | no       | no               | no               |            |            |
      | price   | Price       | prices | yes      | no               | no               |            |            |
      | tax     | Tax         | prices | no       |                  | yes              | 10         | 100        |
      | customs | Customs     | prices | yes      |                  | yes              | 10         | 100        |
    And the following family:
      | code | label-en_US | attributes                     |
      | baz  | Baz         | sku, cost, price, tax, customs |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of price attribute
    Given I change the "Cost" to "-10 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the negative allowed constraint of scopable price attribute
    Given I change the "Price" to "-10 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of price attribute
    Given I change the "Cost" to "2.7 USD"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of scopable price attribute
    Given I change the "Price" to "4.9 USD"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of price attribute
    Given I change the "Tax" to "5.5 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 10 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of scopable price attribute
    Given I change the "Customs" to "9.9 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 10 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of price attribute
    Given I change the "Tax" to "110 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of scopable price attribute
    Given I change the "Customs" to "222.2 USD"
    And I save the product
    Then I should see validation tooltip "This value should be 100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the type constraint of price attribute
    Given I change the "Tax" to "bar USD"
    And I change the "Tax" to "qux EUR"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    And there should be 1 error in the "Other" tab
