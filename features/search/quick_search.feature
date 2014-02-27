@javascript
Feature: Search in Akeneo PIM
  In order to search something in the catalog
  As a user
  I need to be able to search what I want

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And a "product_test" product

  Scenario: Use the search form
    Given I am on the search page
    When I fill in the following information:
      | search | p |
    And I press the "Search" button
    Then I should see "Peter Doe"
    And I should see "product_test"

  Scenario: Use the quick search form
    Given I am on the search page
    When I fill in the following information in the quick search popin:
      | search | p |
      | type   | Product |
    And I press the "Go" button
    Then I should see "product_test"
    And I should not see "Peter Doe"

  @jira https://akeneo.atlassian.net/browse/PIM-2011
  Scenario: Only display Category, User and Product types for search
    Given I am on the search page
    When I open the quick search popin
    Then I can search by the following types:
      | type     |
      | Category |
      | Product  |
      | User     |
    And I can not search by the following types:
      | type     |
      | Email |
      | Tag   |
