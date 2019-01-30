@javascript
Feature: List proposals
  In order to easily view, approve and refuse proposals
  As a product manager
  I need to be able to view a list of all proposals

  Background:
    Given an "apparel" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
    And the following products:
      | sku    | family  | categories      |
      | hoodie | jackets | 2014_collection |
    And Mary proposed the following change to "hoodie":
      | field | value              |
      | Name  | Hoodie for hackers |

  Scenario: Display a custom message when there is no proposal available
    Given I am logged in as "Sandra"
    And I am on the proposals page
    Then the grid should contain 0 elements
    And I should see the text "There is no proposal to review."
    And I should not see the text "There is no proposal to review. Try to change your search criteria."

  Scenario: Filter the proposal grid and display a custom message if there is no results
    When I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 elements
    And I filter by "createdAt" with operator "between" and value "02/01/2012 and 02/02/2012"
    Then the grid should contain 0 elements
    And I should see the text "There is no proposal to review. Try to change your search criteria."
