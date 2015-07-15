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
    And the following category accesses:
      | category        | user group | access |
      | 2014_collection | Manager    |        |
      | shoes           | Manager    | edit   |
      | vintage         | Manager    | edit   |
      | trendy          | Manager    | edit   |
      | classy          | Manager    | edit   |
      | boots           | Manager    | view   |
    And the following products:
      | sku     | categories      | name-en_US |
      | rangers | vintage, classy | rangers    |
      | boots   | boots           | boots      |

  # TODO: Un-skip this scenario in PIM-4251
  @javascript @skip-pef @skip
  Scenario: Successfully create, edit and save a product I have access
    Given I am on the "rangers" product page
    And I fill in the following information:
      | Name | My Rangers |
    When I press the "Save" button
    Then I should be on the product "rangers" edit page
    Then the product Name should be "My Rangers"

  # TODO: Un-skip this scenario in PIM-4251
  @javascript @skip
  Scenario: Seeing the edit actions on the product grid
    Given I am on the products page
    And I select the "Boots" tree
    Then I should not be able to view the "View the product" action of the row which contains "boots"
    And I should be able to view the "Edit attributes of the product" action of the row which contains "boots"
    And I should be able to view the "Classify the product" action of the row which contains "boots"
    And I should be able to view the "Delete the product" action of the row which contains "boots"

  @skip
  Scenario: Not being able to edit a product I have not access
    Given I am on the products page
    Then I should not be able to access the "boots" product page
