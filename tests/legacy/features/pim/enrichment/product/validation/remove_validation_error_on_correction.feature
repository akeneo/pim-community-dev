@javascript
Feature: Validate that validation is removed on correction
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors and no validation error on correction

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code | label-en_US | type                         | scopable | decimals_allowed | number_min | number_max | group |
      | cost | Cost        | pim_catalog_price_collection | 0        | 0                |            |            | other |
    And the following family:
      | code | label-en_US | attributes |
      | baz  | Baz         | sku,cost   |
    And the following product:
      | sku | family |
      | foo | baz    |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate that the validation error is removed on error correction
    Given I change the "Cost" to "10.9 USD"
    And I save the product
    Then I should see validation tooltip "This value should not be a decimal."
    And there should be 1 error in the "Other" tab
    Then I change the "Cost" to "10 USD"
    And I save the product
    And there should be 0 error in the "Other" tab
    Then I should not see validation tooltip "This value should not be a decimal."
