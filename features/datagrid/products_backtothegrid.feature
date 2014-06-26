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
    And I am on the products page

  Scenario: Successfully restore filters without hashnav
    Given I filter by "SKU" with value "boots_1"
    And I am on the products page
    Then the grid should contain 1 element
    And I should see "SKU: contains \"boots_1\""
    And I should see product boots_1
    And I should not see product sneakers_1

  Scenario: Successfully restore filters with hashnav
    Given I filter by "SKU" with value "sneakers_1"
    And I click on the "sneakers_1" row
    And I click back to grid
    Then the grid should contain 1 element
    And I should see "SKU: contains \"sneakers_1\""
    And I should see product sneakers_1
    And I should not see product boots_1
