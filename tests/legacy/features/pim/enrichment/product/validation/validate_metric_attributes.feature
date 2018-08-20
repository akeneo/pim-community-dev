@javascript
Feature: Validate metric attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for metric attributes

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | label-en_US | type               | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | area   | Area        | pim_catalog_metric | 0        | Area          | HECTARE             | 0                | 0                |            |            | other |
      | length | Length      | pim_catalog_metric | 1        | Length        | METER               | 0                | 0                |            |            | other |
      | power  | Power       | pim_catalog_metric | 0        | Power         | WATT                | 1                | 1                | -200       | -100       | other |
      | speed  | Speed       | pim_catalog_metric | 1        | Speed         | YARD_PER_HOUR       | 1                | 1                | 5.50       | 100        | other |
    And the following family:
      | code | label-en_US | attributes                  |
      | baz  | Baz         | sku,area,length,power,speed |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of metric attribute
    Given I change the Area to "-10 Hectare"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the negative allowed constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Length to "-10 Meter"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of metric attribute
    Given I change the Area to "2.7 Hectare"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the decimals allowed constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Length to "4.9 Meter"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of metric attribute
    Given I change the Power to "-250 Watt"
    And I save the product
    Then I should see validation tooltip "This value should be -200 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number min constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Speed to "-7.5 Yard per hour"
    And I save the product
    Then I should see validation tooltip "This value should be 5.5 or more."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of metric attribute
    Given I change the Power to "10 Watt"
    And I save the product
    Then I should see validation tooltip "This value should be -100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the number max constraint of scopable metric attribute
    Given I switch the scope to "ecommerce"
    And I change the Speed to "111.1 Yard per hour"
    And I save the product
    Then I should see validation tooltip "This value should be 100 or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the type constraint of metric attribute
    Given I change the Power to "bar Watt"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    Then I should see validation tooltip "This value should be -100 or less."
    And there should be 2 error in the "Other" tab
