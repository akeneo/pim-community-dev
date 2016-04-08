Feature: Deactivate a currency and edit a product
  In order to use the enriched product data
  As a product manager
  I need to be able to deactivate a currency

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | price          | size | color | name-en_US |
      | Sneakers | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black | Sneakers   |
    And I am logged in as "Julia"
    When I am on the "tablet" channel page
    And I change the Currencies to "EUR"
    Then I save the channel
    When I am on the currencies page
    And I filter by "Activated" with value "yes"
    Then I deactivate the USD currency

  @javascript
  Scenario: Can edit product with disable currency
    Given I am on the "Sneakers" product page
    And I visit the "Marketing" group
    Then I should not see the text "USD"
    When I save the product
    Then I should see the text "Product successfully updated"
