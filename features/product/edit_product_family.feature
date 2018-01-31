@javascript
Feature: Edit the family of a product
  In order to manage the product family
  As a product manager
  I need to be able to change the family of a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   |
      | sneakers | sneakers |
      | sandals  | sandals  |
    And I am logged in as "Julia"

  Scenario: Successfully empty the product family
    Given I am on the "sneakers" product page
    Then I should see the text "Family Sneakers"
    When I change the family of the product to ""
    Then I should see the text "Family None"
    When I save the product
    Then I should see the text "Family None"

  Scenario: Successfully change the product family
    Given I am on the "sneakers" product page
    Then I should see the text "Family Sneakers"
    When I change the family of the product to "Boots"
    Then I should see the text "Family Boots"
    When I save the product
    Then I should see the text "Family Boots"

  Scenario: Successfully change the product family searching by label
    Given I am on the "sneakers" product page
    Then I should see the text "Family Sneakers"
    When I change the family of the product to "LED TVs"
    Then I should see the text "Family LED TVs"
    When I save the product
    Then I should see the text "Family LED TVs"
