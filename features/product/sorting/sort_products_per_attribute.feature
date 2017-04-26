@javascript
Feature: Sort products per attributes
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per attributes

  Background:
    Given the "apparel" catalog configuration
    Given the following products:
      | sku          | family  |
      | blue_shirt   | tshirts |
      | red_shirt    | tshirts |
      | green_shirt  | tshirts |
      | yellow_shirt | tshirts |
      | orange_shirt | tshirts |
    And I am logged in as "Mary"

  Scenario: Successfully sort products by sku
    Given I am on the products page
    And the grid should contain 5 elements
    And I should be able to sort the rows by SKU

  Scenario: Successfully sort products by boolean value for boolean attributes
    And I am on the "blue_shirt" product page
    And I visit the "Additional information" group
    When I check the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the "orange_shirt" product page
    And I visit the "Additional information" group
    When I check the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    Then the grid should contain 5 elements
    When I display the columns SKU, Label, Family, Status, Complete, Created at, Updated at, Groups and Handmade
    Then I should be able to sort the rows by Handmade
