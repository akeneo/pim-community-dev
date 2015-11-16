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
      | 2015_collection  | Redactor   | edit   |
      | 2014_collection  | IT Support | view   |
      | 2015_collection  | Manager    | edit   |
    And the following products:
      | sku     | family   | categories      |
      | tshirt  | tshirts  | 2015_collection |
      | sweater | sweaters | 2015_collection |
      | hoodie  | jackets  | 2014_collection |
      | jacket  | jackets  | 2015_collection |
    And Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And Sandra proposed the following change to "sweater":
      | field | value          |
      | Name  | Winter sweater |
    And Julia proposed the following change to "jacket":
      | field       | value         | tab     |
      | Name        | Autumn jacket | General |
      | Price       | 10 USD        | Sales   |
    And Mary proposed the following change to "hoodie":
      | field | value              |
      | Name  | Hoodie for hackers |

  Scenario: Successfully sort and filter proposals in the grid
    Given I am logged in as "admin"
    And I am on the proposals page
    Then the grid should contain 3 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author and proposed at
    And I should be able to use the following filters:
      | filter        | value             | result                  |
      | Author        | Julia             | jacket                  |
      | Author        | Sandra,Mary       | sweater, tshirt         |
      | Product label | tshirt            | tshirt                  |
      | Product label | tshirt,jacket     | tshirt, jacket          |
      | Attribute     | Name              | tshirt, sweater, jacket |
      | Attribute     | Description       | tshirt                  |
      | Attribute     | Price             | jacket                  |
      | Attribute     | Description,Price | tshirt, jacket          |

  Scenario: Successfully approve or reject a proposal
    Given I am logged in as "admin"
    And I am on the proposals page
    Then the grid should contain 3 elements
    And I should see entities tshirt, sweater and jacket
    When I click on the "Approve" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    Then I should see a flash message "The proposal has been applied successfully."
    And the grid should contain 2 elements
    When I click on the "Reject" action of the row which contains "jacket"
    And I press the "Send" button in the popin
    Then I should see a flash message "The proposal has been refused."
    And the grid should contain 1 element

  Scenario: Successfully display only proposals that the current user can approve
    Given I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 1 elements
    And I should see entities hoodie
