@javascript @permission-feature-enabled
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

  Scenario: Display only granted products in products grid, I see all products
    Given I am logged in as "Mary"
    When I am on the products grid
    Then the grid should contain 3 elements

  Scenario: Display only granted products in products grid, I see a sub set of products
    Given I am logged in as "Mary"
    And I edit the "summer_collection" category
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Manager |
      | Allowed to edit products | Manager |
      | Allowed to own products  | Manager |
    And I submit the category changes
    Then I am on the products grid
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
    And I open the category tab "Permissions"
    And I fill in the category permission with:
      | Allowed to view products | Manager |
    And I submit the category changes
    When I am on the products grid
    Then the grid should contain 4 elements
    And I should see products unclassifiedOne, unclassifiedTwo, inProtectedTree and inProtectedNode

  # @jira https://akeneo.atlassian.net/browse/PIM-5402
  @critical
  Scenario: Successfully manage a product category when there is no permission
    Given I am logged in as "Mary"
    And I edit the "2014_collection" category
    And I open the category tab "Permissions"
    When I remove all the category permission from "Allowed to view products"
    And I submit the category changes
    And I go to the category tree "2014_collection" page
    Then I should see the text "Winter collection"
    And I should see the text "Summer collection"
