@javascript
Feature: View and access products belonging to the same variant group from the product edit page
  In order to easily access more information about the variant group of the product I am editing
  As a product manager
  I need to see an overview of the products belonging to the same variant group from the product edit page

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku         | color | size | groups        |
      | big_boot    |       |      | similar_boots |
      | small_boot  |       |      | similar_boots |
      | medium_boot |       |      | similar_boots |
      | sandals     |       |      | similar_boots |
      | dance_shoe  |       |      | similar_boots |
      | safety_shoe |       |      | similar_boots |
      | tennis_shoe |       |      | similar_boots |
    And I am logged in as "Julia"

  Scenario: Successfully display an overview of products belonging to the same variant group
    Given I am on the "big_boot" product page
    When I press the "Similar boots" button
    Then I should see the text "big_boot"
    And I should see the text "small_boot"
    And I should see the text "medium_boot"
    And I should see the text "sandals"
    And I should see the text "dance_shoe"
    And I should see the text "2 more products"

  Scenario: Successfully go on the page of a product belonging to the same variant group
    Given I am on the "big_boot" product page
    When I press the "Similar boots" button
    When I press the "dance_shoe" button in the popin
    Then I should be on the product "dance_shoe" edit page
