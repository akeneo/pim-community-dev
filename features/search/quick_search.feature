@javascript
Feature: Search in Akeneo PIM
  In order to search something in the catalog
  As a user
  I need to be able to search what I want

  Scenario: Use the search form
    Given the "default" catalog configuration
    And a "product_test" product
    And I am logged in as "admin"
    When I am on the search page
    And I fill in the following information:
      | search | p |
    And I press the "Search" button
    Then I should see "Peter Doe"
    And I should see "product_test"
