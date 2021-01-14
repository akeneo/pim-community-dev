@javascript
Feature: Classify many products at once for the tree I have access
  In order to easily classify products
  As a product manager
  I need to associate many products to categories I have access at once

  Background:
    Given the "clothing" catalog configuration
    And the following categories:
      | code     | label-en_US | parent |
      | shoes    | Shoes       |        |
      | vintage  | Vintage     | shoes  |
      | trendy   | Trendy      | shoes  |
      | classy   | Classy      | shoes  |
      | boots    | Boots       |        |
      | sneakers | Sneakers    | shoes  |
      | sandals  | Sandals     | shoes  |
    And the following product category accesses:
      | product category | user group | access |
      | shoes            | Manager    | view   |
      | vintage          | Manager    | view   |
      | trendy           | Manager    | view   |
      | classy           | Manager    | view   |
      | sandals          | Manager    | own    |
      | sneakers         | Manager    | own   |
      | boots            | IT support | none   |
      | 2014_collection  | Manager    | own    |
    And the following products:
      | sku     | categories      |
      | rangers | 2014_collection |
      | loafer  | 2014_collection |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Add several products to categories at once
    Given I select rows rangers and loafer
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    Then I should not see the text "Boots"
    And I should not see the text "Master catalog"
    When I select the "Shoes" tree
    And I expand the "Shoes" category
    And I click on the "Vintage" category
    And I click on the "Classy" category
    And I confirm mass edit
    And I wait for the "add_to_category" job to finish
    And I am on the products grid
    And I open the category tree
    And I select the "Shoes" tree
    Then I should see the text "Vintage (2)"
    And I should see the text "Classy (2)"

  Scenario: Move several products to categories at once
    Given I select rows rangers and loafer
    And I press the "Bulk actions" button
    And I choose the "Move between categories" operation
    Then I should not see the text "Boots"
    And I should not see the text "Master catalog"
    When I select the "Shoes" tree
    And I expand the "Shoes" category
    And I click on the "Sandals" category
    And I click on the "Sneakers" category
    And I confirm mass edit
    And I wait for the "move_to_category" job to finish
    And I am on the products grid
    And I open the category tree
    And I select the "Shoes" tree
    Then I should see the text "Sandals (2)"
    And I should see the text "Sneakers (2)"

  Scenario: Failed to move several products to viewable categories
    Given I select rows rangers and loafer
    And I press the "Bulk actions" button
    And I choose the "Move between categories" operation
    Then I should not see the text "Boots"
    And I should not see the text "Master catalog"
    When I select the "Shoes" tree
    And I expand the "Shoes" category
    And I click on the "Vintage" category
    And I click on the "Classy" category
    And I confirm mass edit
    And I wait for the "move_to_category" job to finish
    And I am on the products grid
    And I open the category tree
    And I select the "Shoes" tree
    Then I should see the text "Vintage (0)"
    And I should see the text "Classy (0)"
