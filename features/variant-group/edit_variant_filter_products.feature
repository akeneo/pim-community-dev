@javascript
Feature: Filter available products for a variant group
  In order to easily browse products inside a variant group
  As a user
  I need to be able to filter products in a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   |
      | black_boots | boots    |
      | white_boots | boots    |
      | blue_boots  | sneakers |
      | sneakers    | sneakers |
    And the following product values:
      | product      | attribute   | value             | locale | scope  |
      | black_boots  | color       | black             |        |        |
      | white_boots  | color       | white             |        |        |
      | blue_boots   | color       | blue              |        |        |
      | sneakers     | color       | black             |        |        |
      | black_boots  | size        | 41                |        |        |
      | white_boots  | size        | 42                |        |        |
      | blue_boots   | size        | 43                |        |        |
      | black_boots  | name        | Black boots       | en_US  |        |
      | white_boots  | name        | White boots       | en_US  |        |
      | blue_boots   | name        | Blue boots        | en_US  |        |
      | black_boots  | description | Nice boots        | en_US  | mobile |
      | black_boots  | description | Nice shiny boots  | en_US  | tablet |
      | white_boots  | description | Great boots       | en_US  | mobile |
      | white_boots  | description | Great shiny boots | en_US  | tablet |
      | blue_boots   | description | Nice boots        | en_US  | mobile |
      | blue_boots   | description | Nice shiny boots  | en_US  | tablet |
      | black_boots  | price       | 45 EUR, 60 USD    |        |        |
      | white_boots  | price       | 50 EUR, 65 USD    |        |        |
      | blue_boots   | price       | 45 EUR, 60 USD    |        |        |
      | sneakers     | price       | 40 EUR, 55 USD    |        |        |
    And I am logged in as "admin"
    And I am on the "caterpillar_boots" variant group page

  Scenario: Successfully filter the product datagrid when I edit a variant group
    Then the grid should contain 3 elements
    And I should see products black_boots, white_boots and blue_boots
    And I should not see product sneakers
    And I should be able to use the following filters:
      | filter      | value    | result                                  |
      | Has product | no       | black_boots, white_boots and blue_boots |
      | SKU         | bl       | black_boots and blue_boots              |
      | Family      | Sneakers | blue_boots                              |
      | Color       | Black    | black_boots                             |
      | Size        | 42       | white_boots                             |
      | Name        | bl       | black_boots and blue_boots              |
      | Description | great    | white_boots                             |
      | Price       | < 47 EUR | black_boots and blue_boots              |
    And I should not see the filters Created at and Updated at
