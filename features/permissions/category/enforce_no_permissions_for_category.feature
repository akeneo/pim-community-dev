@javascript
Feature: Enforce no permissions for a category
  In order to be able to prevent some users from viewing some products
  As an administrator
  I need to be able to enforce no permissions for categories

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku        | categories        | main_color | size |
      | grantedOne | winter_collection |            |      |
      | grantedTwo | winter_collection |            |      |
      | notGranted | summer_collection | white      | L    |

  Scenario: Redirect users from the product page to the dashboard when they can't see products in any tree
    Given I am logged in as "Mary"
    And I am on the "2014_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | View products | Manager |
      | Edit products | Manager |
      | Own products  | Manager |
    And I save the category
    And I am on the products page
    Then I should be on the homepage
    Then I should see "You don't have access to products in any tree, please contact your administrator"

  Scenario: Display only granted products in products grid, I see all products
    Given I am logged in as "Mary"
    And I am on the products page
    And the grid should contain 3 elements

  Scenario: Display only granted products in products grid, I see a sub set of products
    Given I am logged in as "Mary"
    And I am on the "summer_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | View products | Manager |
      | Edit products | Manager |
      | Own products  | Manager |
    And I save the category
    And I am on the products page
    And the grid should contain 2 elements

  Scenario: Display only granted products in association products grid, I see all products
    Given I am logged in as "Julia"
    And I edit the "grantedOne" product
    When I visit the "Associations" tab
    Then the grid should contain 2 elements

  Scenario: Display only granted products in association products grid, I see a sub set of products
    Given I am logged in as "Julia"
    And I am on the "summer_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | View products | Administrator |
      | Edit products | Administrator |
      | Own products  | Administrator |
    And I save the category
    And I edit the "grantedOne" product
    When I visit the "Associations" tab
    Then the grid should contain 1 elements

  Scenario: Display only granted products in variant group products grid, I see all products
    Given I am logged in as "Mary"
    And I am on the "hm_jackets" variant group page
    Then the grid should contain 1 elements

  Scenario: Display only granted products in variant group products grid, I see a sub set of products
    Given I am logged in as "Mary"
    And I am on the "summer_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | View products | Manager |
      | Edit products | Manager |
      | Own products  | Manager |
    And I save the category
    Given I am on the "hm_jackets" variant group page
    Then the grid should contain 0 elements
