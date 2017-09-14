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

  @skip-nav
  Scenario: Redirect users from the product page to the dashboard when they can't see products in any tree
    Given I am logged in as "Mary"
    And I am on the "2014_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Manager |
      | Allowed to edit products | Manager |
      | Allowed to own products  | Manager |
    And I save the category
    And I am on the products grid
    Then I should be on the homepage
    Then I should see the text "You don't have access to products in any tree, please contact your administrator"

  Scenario: Display only granted products in products grid, I see all products
    Given I am logged in as "Mary"
    And I am on the products grid
    And the grid should contain 3 elements

  Scenario: Display only granted products in products grid, I see a sub set of products
    Given I am logged in as "Mary"
    And I am on the "summer_collection" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Manager |
      | Allowed to edit products | Manager |
      | Allowed to own products  | Manager |
    And I save the category
    And I am on the products grid
    And the grid should contain 2 elements

  Scenario: Display only granted products in products grid when filtering by unclassified
    Given the following categories:
      | code           | parent         |
      | protected_tree |                |
      | protected_node | protected_tree |
    And the following product category accesses:
      | product category | user group | access |
      | protected_tree   | Manager    | none   |
      | protected_tree   | IT Support | view   |
      | protected_node   | Manager    | none   |
      | protected_node   | IT Support | view   |
    And the following products:
      | sku             | categories     |
      | unclassifiedOne |                |
      | unclassifiedTwo |                |
      | inProtectedTree | protected_tree |
      | inProtectedNode | protected_node |
    And I am logged in as "Julia"
    And I am on the products grid
    And I open the category tree
    When I filter by "category" with operator "unclassified" and value ""
    And I close the category tree
    Then the grid should contain 2 elements
    And I should see products unclassifiedOne and unclassifiedTwo
    But I should not see products inProtectedTree and inProtectedNode
    When I am on the "protected_tree" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Manager |
    And I save the category
    When I am on the products grid
    Then the grid should contain 4 elements
    And I should see products unclassifiedOne, unclassifiedTwo, inProtectedTree and inProtectedNode

  Scenario: Display only granted products in association products grid, I see all products
    Given I am logged in as "Julia"
    And I edit the "grantedOne" product
    When I visit the "Associations" column tab
    And I wait 3 seconds
    Then the grid should contain 2 elements

  Scenario: Display only granted products in association products grid, I see a sub set of products
    Given I am logged in as "Julia"
    And the following product category accesses:
      | product category  | user group | access |
      | winter_collection | Manager    | own    |
      | summer_collection | IT support | all    |
    When I edit the "grantedOne" product
    And I visit the "Associations" column tab
    Then the grid should contain 1 elements

  @jira https://akeneo.atlassian.net/browse/PIM-5402
  Scenario: Successfully manage a product category when there is no permission
    Given I am logged in as "Mary"
    When I edit the "2014_collection" category
    And I visit the "Permissions" tab
    And I fill in "Allowed to view products" with "" on the current page
    And I save the category
    Then I should see the flash message "Tree successfully updated"
    When I refresh current page
    Then I should see the "winter_collection" category under the "2014_collection" category
    And I should see the "summer_collection" category under the "2014_collection" category
