@javascript
Feature: Go to product group page from the product edit page
  In order to easily access more information about the group of the product I am editing
  As a product manager
  I need to be able to go to the product group page from the product edit page

  Scenario: Successfully display a readonly form for a product in a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku        | color | size | groups            |
      | big_boot   | black | 40   | caterpillar_boots |
      | small_boot |       |      | similar_boots     |
    And I am logged in as "Julia"
    When I am on the "big_boot" product page
    And I press the "Caterpillar boots" button
    And I press the "View group" button
    Then I should be on the "caterpillar_boots" variant group page
    When I am on the "small_boot" product page
    And I press the "Similar boots" button
    And I press the "View group" button
    Then I should be on the "similar_boots" product group page
