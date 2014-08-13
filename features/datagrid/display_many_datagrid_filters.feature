@javascript
Feature: Display many datagrid filters
  In order to be able to have many filterable attributes
  As a regular user
  I need to be able to use the datagrid with many filters

  Scenario: Successfully display the datagrid with 500 filters
    Given the "default" catalog configuration
    And 500 filterable simple select attributes with 5 options per attribute
    And I am logged in as "Mary"
    And I am on the products page
    When I show the filter "Attribute 499"
    And I filter by "Attribute 499" with value "Option 1 for attribute 499"
    Then I should be on the products page
