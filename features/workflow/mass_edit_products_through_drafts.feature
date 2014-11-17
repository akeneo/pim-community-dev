@javascript
Feature: Mass edit products through product draft
  In order to prevent changes on product when I am not allowed to
  As a contributor
  I need to be able to propose values without changing actual product values through mass edit

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                    | jackets                 |
      | categories                | winter_top              |
      | sku                       | my-first-jacket         |
      | name-en_US                | First jacket      |
      | description-en_US-mobile  | An awesome first jacket |
      | number_in_stock-ecommerce | 2                       |
      | number_in_stock-mobile    | 4                       |
      | number_in_stock-print     | 5                       |
      | number_in_stock-tablet    | 20                      |
      | price-USD                 | 45                      |
      | manufacturer              | Volcom                  |
      | weather_conditions        | dry, wet                |
      | handmade                  | no                      |
      | release_date-ecommerce    | 2014-05-14              |
      | length                    | 60 CENTIMETER           |
    And the product:
      | family                    | jackets                  |
      | categories                | winter_top               |
      | sku                       | my-second-jacket         |
      | name-en_US                | Second jacket            |
      | description-en_US-mobile  | An awesome second jacket |
      | number_in_stock-ecommerce | 2                        |
      | number_in_stock-mobile    | 4                        |
      | number_in_stock-print     | 5                        |
      | number_in_stock-tablet    | 20                       |
      | price-USD                 | 45                       |
      | manufacturer              | Volcom                   |
      | weather_conditions        | dry, wet                 |
      | handmade                  | no                       |
      | release_date-ecommerce    | 2014-05-14               |
      | length                    | 60 CENTIMETER            |
    And I am logged in as "Mary"
    And I am on the products page

  Scenario: Succesfully create simple text attribute product draft of many products
    Given I mass-edit products my-first-jacket and my-second-jacket
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "A jacket"
    And I move on to the next step
    Then the english name of "my-first-jacket" should be "First jacket"
    And the english name of "my-second-jacket" should be "Second jacket"
    But Mary should have proposed the following values for products my-first-jacket and my-second-jacket:
      | attribute | value    |
      | Name      | A jacket |
