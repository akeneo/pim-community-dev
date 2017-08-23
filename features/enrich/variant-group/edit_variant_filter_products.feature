@javascript
Feature: Filter available products for a variant group
  In order to easily browse products inside a variant group
  As a product manager
  I need to be able to filter products in a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku            | family   | color | size | price          | name-en_US  | description-en_US-mobile | description-en_US-tablet |
      | black_boots    | boots    | black | 41   | 45 EUR, 60 USD | Black boots | Nice boots               | Nice shiny boots         |
      | white_boots    | boots    | white | 42   | 50 EUR, 65 USD | White boots | Great boots              | Great shiny boots        |
      | blue_boots     | sneakers | blue  | 43   | 45 EUR, 60 USD | Blue boots  | Nice boots               | Nice shiny boots         |
      | black_sneakers | sneakers | black |      | 40 EUR, 55 USD |             |                          |                          |
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page

  Scenario Outline: Successfully filter the product datagrid when I edit a variant group
    Then the grid should contain 3 elements
    And I should see products black_boots, white_boots and blue_boots
    And I should not see product black_sneakers
    And I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter      | operator | value    | result                                  | count |
      | in_group    |          | no       | black_boots, white_boots and blue_boots | 3     |
      | sku         | Contains | bl       | black_boots and blue_boots              | 2     |
      | family      | In List  | Sneakers | blue_boots                              | 1     |
      | color       | In List  | Black    | black_boots                             | 1     |
      | size        | In List  | 42       | white_boots                             | 1     |
      | name        | Contains | bl       | black_boots and blue_boots              | 2     |
      | description | Contains | great    | white_boots                             | 1     |
      | price       | <        | 47 EUR   | black_boots and blue_boots              | 2     |
