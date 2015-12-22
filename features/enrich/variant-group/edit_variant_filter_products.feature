@javascript
Feature: Filter available products for a variant group
  In order to easily browse products inside a variant group
  As a product manager
  I need to be able to filter products in a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   | color | size | price          | name-en_US  | description-en_US-mobile | description-en_US-tablet |
      | black_boots | boots    | black | 41   | 45 EUR, 60 USD | Black boots | Nice boots               | Nice shiny boots         |
      | white_boots | boots    | white | 42   | 50 EUR, 65 USD | White boots | Great boots              | Great shiny boots        |
      | blue_boots  | sneakers | blue  | 43   | 45 EUR, 60 USD | Blue boots  | Nice boots               | Nice shiny boots         |
      | sneakers    | sneakers | black |      | 40 EUR, 55 USD |             |                          |                          |
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page

  Scenario: Successfully filter the product datagrid when I edit a variant group
    Then the grid should contain 3 elements
    And I should see products black_boots, white_boots and blue_boots
    And I should not see product sneakers
    And I should be able to use the following filters:
      | filter      | value    | result                                  |
      | In group    | no       | black_boots, white_boots and blue_boots |
      | SKU         | bl       | black_boots and blue_boots              |
      | Family      | Sneakers | blue_boots                              |
      | Color       | Black    | black_boots                             |
      | Size        | 42       | white_boots                             |
      | Name        | bl       | black_boots and blue_boots              |
      | Description | great    | white_boots                             |
      | Price       | < 47 EUR | black_boots and blue_boots              |
