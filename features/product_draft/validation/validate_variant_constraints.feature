@javascript
Feature:
  In order to have only one combination of variant of product in a variant group
  as Julia
  I need to be prevented from saving a variant product with an existing combination

  @info https://akeneo.atlassian.net/browse/PIM-2113
  Scenario: Fail to save a variant product with an existing combination
    Given a "clothing" catalog configuration
    And the following products:
      | sku                 | family  | main_color | size | groups     | categories        |
      | black_jackets       | jackets | black      | M    | hm_jackets | summer_collection |
      | other_black_jackets | jackets | black      | S    | hm_jackets | summer_collection |
    And I am logged in as "Mary"
    When I am on the "other_black_jackets" product page
    And I visit the "Sizes" group
    And I change the "Size" to "M"
    And I save the product
    Then I should see the text "Group \"H&M jackets\" already contains another product with values \"size: M, main_color: Black\""
