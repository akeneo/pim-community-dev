@javascript
Feature: List proposals
  In order to easily view, approve and refuse proposals
  As a product manager
  I need to be able to view a list of all proposals

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku     | family   |
      | tshirt  | tshirts  |
      | sweater | sweaters |
      | jacket  | jackets  |
    And the following product drafts:
      | product | status | author |
      | tshirt  | ready  | Julia  |
      | sweater | ready  | Sandra |
      | jacket  | ready  | Mary   |
    And I am logged in as "Julia"
    And I am on the proposals page

  Scenario: Successfully sort and filter proposals in the grid
    Then the grid should contain 3 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author and proposed at
    And I should be able to use the following filters:
      | filter      | value                | result                     |
      | Author      | Julia                | tshirt                     |
      | Author      | Sandra,Mary          | sweater, jacket            |
      | Proposed at | more than 2012-01-01 | tshirt, sweater and jacket |

  Scenario: Successfully approve or refuse a proposal
    Given I click on the "Approve" action of the row which contains "tshirt"
    Then I should see a flash message "The proposal has been applied successfully."
    And the grid should contain 2 elements
    When I click on the "Refuse" action of the row which contains "jacket"
    Then I should see a flash message "The proposal has been refused."
    And the grid should contain 1 element
