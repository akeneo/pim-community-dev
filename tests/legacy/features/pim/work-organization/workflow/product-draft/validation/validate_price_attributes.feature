@javascript
Feature: Validate price attributes of a draft
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for price attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                         | scopable | decimals_allowed | number_min | number_max | group |
      | cost      | Cost        | pim_catalog_price_collection | 0        | 0                |            |            | info  |
      | net_price | Price       | pim_catalog_price_collection | 1        | 0                |            |            | info  |
      | tax       | Tax         | pim_catalog_price_collection | 0        | 1                | 10         | 100        | info  |
      | customs   | Customs     | pim_catalog_price_collection | 1        | 1                | 10         | 100        | info  |
    And the following family:
      | code | label-en_US | attributes                     |
      | baz  | Baz         | sku,cost,net_price,tax,customs |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the decimals allowed constraint of price attribute
    Given I change the Cost to "2.7 USD"
    And I save the product
    Then I should see validation error "The cost attribute requires a non-decimal value, and 2.7 is not a valid value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the decimals allowed constraint of scopable price attribute
    Given I change the Price to "4.9 EUR"
    And I save the product
    Then I should see validation error "The net_price attribute requires a non-decimal value, and 4.9 is not a valid value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number min constraint of price attribute
    Given I change the Tax to "5.5 USD"
    And I save the product
    Then I should see validation error "The tax attribute requires an equal or greater than 10 value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number min constraint of scopable price attribute
    Given I change the Customs to "9.9 EUR"
    And I save the product
    Then I should see validation error "The customs attribute requires an equal or greater than 10 value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number max constraint of price attribute
    Given I change the Tax to "110 USD"
    And I save the product
    Then I should see validation error "The tax attribute requires an equal or lesser than 100 value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the number max constraint of scopable price attribute
    Given I change the Customs to "222.2 EUR"
    And I save the product
    Then I should see validation error "The customs attribute requires an equal or lesser than 100 value."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the type constraint of price attribute
    Given I change the Tax to "bar USD"
    And I change the Tax to "qux EUR"
    And I save the product
    Then I should see validation error "The tax attribute requires a number, and the submitted bar value is not."
    Then I should see validation error "The tax attribute requires a number, and the submitted qux value is not."
    Then I should see validation error "The tax attribute requires an equal or greater than 10 value."
    And there should be 3 error in the "Product information" tab
