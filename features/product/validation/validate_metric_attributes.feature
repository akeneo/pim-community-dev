@javascript
Feature: Validate metric attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for metric attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | label-en_US | type   | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max |
      | area   | Area        | metric | no       | Area          | HECTARE             | no               | no               |            |            |
      | length | Length      | metric | yes      | Length        | METER               | no               | no               |            |            |
      | power  | Power       | metric | no       | Power         | WATT                | yes              | yes              | -200       | -100       |
      | speed  | Speed       | metric | yes      | Speed         | YARD_PER_HOUR       | yes              | yes              | 5.50       | 100        |
    And the following family:
      | code | label-en_US | attributes                      |
      | baz  | Baz         | sku, area, length, power, speed |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of metric attribute
    Given I change the Area to "-10 HECTARE"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the negative allowed constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Length to "-10 METER"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of metric attribute
    Given I change the Area to "2.7 HECTARE"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Length to "4.9 METER"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of metric attribute
    Given I change the Power to "-250 WATT"
    And I save the product
    Then I should see validation tooltip "This value should be -200 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Speed to "-7.5 YARD_PER_HOUR"
    And I save the product
    Then I should see validation tooltip "This value should be 5.5 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of metric attribute
    Given I change the Power to "10 WATT"
    And I save the product
    Then I should see validation tooltip "This value should be -100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Speed to "111.1 YARD_PER_HOUR"
    And I save the product
    Then I should see validation tooltip "This value should be 100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the type constraint of metric attribute
    Given I change the Power to "bar WATT"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    And there should be 1 error in the "Other" tab
