@javascript
Feature: Search in Akeneo PIM
  In order to search something in the catalog
  As a user
  I need to be able to search what I want

  Background:
    Given the following product:
      | sku          |
      | product_test |
    And the following category:
      | code        | label       |
      | my_category | My Category |
    And I am logged in as "admin"

  Scenario: Use the search form
    Given I am on the search page
    When I fill in the following information:
      | search | e |
    And I press the "Search" button
    Then I should see the column Item