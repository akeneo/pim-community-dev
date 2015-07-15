Feature: Show a product
  In order to consult the catalog
  As a product manager
  I need to be able view a product I can't edit

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following categories:
      | code  | label-en_US |
      | shoes | Shoes       |
      | boots | Boots       |
    And the following category accesses:
      | category        | user group | access |
      | 2014_collection | Manager    |        |
      | shoes           | Manager    | edit   |
      | boots           | Manager    | view   |
    And the following products:
      | sku     | categories | name-en_US      | price            | size | main_color |
      | rangers | shoes      | Classic rangers | 120 EUR, 125 USD | L    | black      |
      | boots   | boots      | Party boots     | 80 EUR, 90 USD   | M    | blue       |

  # TODO: Un-skip this scenario in PIM-4251
  @javascript @skip
  Scenario: Seeing the view actions on the product grid
    Given I am on the products page
    And I select the "Shoes" tree
    Then I should be able to view the "View the product" action of the row which contains "rangers"
    And I should not be able to view the "Edit attributes of the product" action of the row which contains "rangers"
    And I should not be able to view the "Classify the product" action of the row which contains "rangers"
    And I should not be able to view the "Delete the product" action of the row which contains "rangers"

  # TODO: Un-skip this scenario in PIM-4251
  @skip-pef @javascript @skip
  Scenario: Being able to view a product I can not edit
    Given I am on the products page
    And I should be able to access the show "boots" product page
    Then I should not be able to access the edit "boots" product page
    And I should be able to access the edit "rangers" product page

  # TODO: Un-skip this scenario in PIM-4251
  @skip
  Scenario: View a product in read only mode
    When I am on the "boots" product show page
    Then the view mode field SKU should contain "boots"
    And the view mode field Name should contain "Party boots"
    And the view mode field Price should contain "80.00 EUR, 90.00 USD"
    And the view mode field Size should contain "M"
    And the view mode field Main color should contain "Blue"
    And I should not see a single form input
