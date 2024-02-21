@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As an administrator
  I need to be able to see channels

  Scenario: Successfully view, sort channels
    Given an "apparel" catalog configuration
    And I am logged in as "Peter"
    And I am on the channels page
    Then the grid should contain 3 elements
    And I should see the columns Label and Category tree
    And I should see channels Ecommerce, Tablet and Print
    And the rows should be sorted ascending by Label
    And I should be able to sort the rows by Label and Category tree
