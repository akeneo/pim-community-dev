@javascript
Feature:
  In order to have only one combination of variant of product in a variant group
  as Julia
  I need to be prevented from saving a variant product with an existing combination

  @info https://akeneo.atlassian.net/browse/PIM-2113
  Scenario: Fail to save a variant product with an existing combination
    Given a "footwear" catalog configuration
    And the following products:
      | sku               | family | color | size | groups            |
      | black_boots       | boots  | black | 41   | caterpillar_boots |
      | other_black_boots | boots  | black |      | caterpillar_boots |
    And I am logged in as "Mary"
    When I am on the "other_black_boots" product page
    And I visit the "Sizes" group
    And I change the "Size" to "41"
    And I save the product
    Then I should see "Group \"Caterpillar boots\" already contains another product with values \"size: 41, color: Black\""
