@javascript
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
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Manager    |        |
      | shoes            | Manager    | edit   |
      | boots            | Manager    | view   |
    And the following products:
      | sku     | categories | name-en_US      | price            | size | main_color |
      | rangers | shoes      | Classic rangers | 120 EUR, 125 USD | L    | black      |
      | boots   | boots      | Party boots     | 80 EUR, 90 USD   | M    | blue       |

  Scenario: Seeing the view actions on the product grid
    Given I am on the products grid
    And I select the "Shoes" tree
    Then I should be able to view the "View the product" action of the row which contains "rangers"
    And I should not be able to view the "Edit attributes of the product" action of the row which contains "rangers"
    And I should not be able to view the "Classify the product" action of the row which contains "rangers"
    And I should not be able to view the "Delete the product" action of the row which contains "rangers"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4591
  Scenario: Being able to view a product I can not edit
    Given I am on the products grid
    And I should be able to access the show "boots" product page
    Then I should not be able to access the edit "boots" product page
    And I should be able to access the edit "rangers" product page

  Scenario: View a product in read only mode
    When I am on the "boots" product show page
    Then the product SKU should be "boots"
    And the field SKU should be read only
    And the product Name should be "Party boots"
    And the field Name should be read only
    Given I visit the "Marketing" group
    Then the product Price in USD should be "90.00"
    And the field Price should be read only
    Then the product Price in EUR should be "80.00"
    And the field Price should be read only
    Given I visit the "Sizes" group
    And the product Size should be "M"
    And the field Size should be read only
    Given I visit the "Colors" group
    And the product Main color should be "blue"
    And the field Main color should be read only
