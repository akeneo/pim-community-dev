@javascript
Feature: Edit a product I have access
  In order to enrich the catalog
  As a product manager
  I need to be able edit and save a product I have access

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following categories:
      | code    | label-en_US | parent |
      | shoes   | Shoes       |        |
      | vintage | Vintage     | shoes  |
      | trendy  | Trendy      | shoes  |
      | classy  | Classy      | shoes  |
      | boots   | Boots       |        |
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Manager    |        |
      | shoes            | Manager    | edit   |
      | vintage          | Manager    | edit   |
      | trendy           | Manager    | edit   |
      | classy           | Manager    | edit   |
      | boots            | Manager    | view   |
    And the following products:
      | sku     | categories      | name-en_US |
      | rangers | vintage, classy | rangers    |
      | boots   | boots           | boots      |

  @jira https://akeneo.atlassian.net/browse/PIM-4604
  Scenario: Successfully create, edit and save a product I have access
    Given I am on the "rangers" product page
    And I fill in the following information:
      | Name | My Rangers |
    When I save the product
    Then I should be on the product "rangers" edit page
    Then the product Name should be "My Rangers"

  Scenario: Seeing the edit actions on the product grid
    Given I am on the products grid
    And I open the category tree
    And I select the "Boots" tree
    And I close the category tree
    Then I should not be able to view the "View the product" action of the row which contains "boots"
    And I should be able to view the "Edit attributes of the product" action of the row which contains "boots"
    And I should be able to view the "Classify the product" action of the row which contains "boots"
    And I should be able to view the "Delete the product" action of the row which contains "boots"
