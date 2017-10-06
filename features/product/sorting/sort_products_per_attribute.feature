@javascript
Feature: Sort products per attributes
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per attributes

  Scenario: Successfully sort products by sku
    Given the "apparel" catalog configuration
    And the following products:
      | sku          | family  |
      | blue_shirt   | tshirts |
      | red_shirt    | tshirts |
      | green_shirt  | tshirts |
      | yellow_shirt | tshirts |
      | orange_shirt | tshirts |
    And I am logged in as "Mary"
    And I am on the products page
    And the grid should contain 5 elements
    And I should be able to sort the rows by SKU

  Scenario: Successfully sort products by boolean value for boolean attributes
    Given the "apparel" catalog configuration
    And the following products:
      | sku          | family  |
      | blue_shirt   | tshirts |
      | red_shirt    | tshirts |
      | green_shirt  | tshirts |
      | yellow_shirt | tshirts |
      | orange_shirt | tshirts |
    And I am logged in as "Mary"
    And I am on the "blue_shirt" product page
    And I visit the "Additional" group
    When I check the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the "orange_shirt" product page
    And I visit the "Additional" group
    When I check the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    Then the grid should contain 5 elements
    When I display the columns SKU, Label, Family, Status, Complete, Created at, Updated at, Groups and Handmade
    Then I should be able to sort the rows by Handmade

  Scenario: Successfully sort products by simple select attribute options
    Given the "apparel" catalog configuration
    And the following products:
      | sku         | family  | color |
      | black_shirt | tshirts | black |
      | white_shirt | tshirts | white |
      | gray_shirt  | tshirts | gray  |
      | red_shirt   | tshirts | red   |
      | blue_shirt  | tshirts | blue  |
    And I am logged in as "Mary"
    And I am on the products page
    And I display the columns SKU, Label, Color, Family, Size, Status, Complete
    Then I should be able to sort the rows by Color
