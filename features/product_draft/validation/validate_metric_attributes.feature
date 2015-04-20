@javascript
Feature: Validate metric attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for metric attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code       | label-en_US | type   | scopable | metric_family | default_metric_unit | negative_allowed | decimals_allowed | number_min | number_max | group |
      | area       | Area        | metric | no       | Area          | HECTARE             | no               | no               |            |            | info  |
      | max_length | Length      | metric | yes      | Length        | METER               | no               | no               |            |            | info  |
      | power      | Power       | metric | no       | Power         | WATT                | yes              | yes              | -200       | -100       | info  |
      | speed      | Speed       | metric | yes      | Speed         | YARD_PER_HOUR       | yes              | yes              | 5          | 100        | info  |
    And the following family:
      | code | label-en_US | attributes                          |
      | baz  | Baz         | sku, area, max_length, power, speed |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the negative allowed constraint of metric attribute
    Given I change the Area to "-10"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the negative allowed constraint of scopable metric attribute
    Given I change the "mobile Length" to "-10"
    And I save the product
    Then I should see validation tooltip "This value should be 0 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the decimals allowed constraint of metric attribute
    Given I change the Area to "2.7"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the decimals allowed constraint of scopable metric attribute
    Given I change the "mobile Length" to "4.9"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number min constraint of metric attribute
    Given I change the Power to "-250"
    And I save the product
    Then I should see validation tooltip "This value should be -200 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number min constraint of scopable metric attribute
    Given I change the "mobile Speed" to "-5.5"
    And I save the product
    Then I should see validation tooltip "This value should be 5 or more."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number max constraint of metric attribute
    Given I change the Power to "10"
    And I save the product
    Then I should see validation tooltip "This value should be -100 or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the number max constraint of scopable metric attribute
    Given I change the "mobile Speed" to "111.1"
    And I save the product
    Then I should see validation tooltip "This value should be 100 or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the type constraint of metric attribute
    Given I change the Power to "bar"
    And I save the product
    Then I should see validation tooltip "This value should be a valid number."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red
