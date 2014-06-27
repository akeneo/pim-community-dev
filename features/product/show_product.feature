@javascript
Feature: Show a product
  In order to consult the catalog
  As a product manager
  I need to be able view a product I can't edit

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
      | category        | role    | access |
      | 2014_collection | Manager |        |
      | shoes           | Manager | edit   |
      | boots           | Manager | view   |
    And the following products:
      | sku     | categories | name-en |
      | rangers | shoes      | rangers |
      | boots   | boots      | boots   |

  Scenario: Seeing the view actions on the product grid
    Given I am on the products page
    And I select the "Shoes" tree
    Then I should be able to view the "View the product" action of the row which contains "rangers"
    And I should not be able to view the "Edit attributes of the product" action of the row which contains "rangers"
    And I should not be able to view the "Classify the product" action of the row which contains "rangers"
    And I should not be able to view the "Delete the product" action of the row which contains "rangers"

  Scenario: Being able to view a product I can not edit
    Given I am on the products page
    And I should be able to access the show "boots" product page
    Then I should not be able to access the edit "boots" product page
    And I should be able to access the edit "rangers" product page

