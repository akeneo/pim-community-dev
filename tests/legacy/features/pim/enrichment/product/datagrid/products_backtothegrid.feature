@javascript
Feature: Products back to the grid
  In order to restore the product grid filters
  As a regular user
  I need to be able to set filters and retrieve them after going back to the page

  Background:
    Given the "default" catalog configuration
    And a "sneakers_1" product
    And a "boots_1" product
    And I am logged in as "Mary"
    And I am on the products grid

  Scenario: Successfully restore filters
    Given I filter by "sku" with operator "contains" and value "boots_1"
    And the grid should contain 1 element
    And I am on the products grid
    Then the grid should contain 1 element
    And the criteria of "sku" filter should be "contains "boots_1""
    And I should see product boots_1
    And I should not see product sneakers_1
