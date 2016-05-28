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
      | 2015_collection  | Manager    | edit   |
      | 2014_collection  | Manager    | own    |
      | 2015_collection  | IT support | own    |
    And the following products:
      | sku     | family   | categories      | weather_conditions |
      | tshirt  | tshirts  | 2014_collection | dry |
      | sweater | sweaters | 2014_collection | |
      | jacket  | jackets  | 2015_collection | |
    And Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And Sandra proposed the following change to "sweater":
      | field | value          |
      | Name  | Winter sweater |
    And Julia proposed the following change to "jacket":
      | field | value         | tab     |
      | Name  | Autumn jacket | General |
      | Price | 10 USD        | Sales   |

  Scenario: Successfully sort and filter proposals in the grid
    Given I am logged in as "Peter"
    When I am on the proposals page
    Then the grid should contain 1 elements
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    When I am on the proposals page
    Then the grid should contain 3 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author and proposed at
    And I should be able to use the following filters:
      | filter    | operator | value             | result                  |
      | author    |          | Julia             | jacket                  |
      | author    |          | Sandra,Mary       | sweater, tshirt         |
      | product   |          | tshirt            | tshirt                  |
      | product   |          | tshirt,jacket     | tshirt, jacket          |
      | attribute |          | Name              | tshirt, sweater, jacket |
      | attribute |          | Description       | tshirt                  |
      | attribute |          | Price             | jacket                  |
      | attribute |          | Description,Price | tshirt, jacket          |

  Scenario: Successfully approve or reject a proposal
    Given I am logged in as "Peter"
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    And I am on the proposals page
    And I should see entities tshirt, sweater and jacket
    When I click on the "Approve all" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    Then I should see the flash message "The proposal has been applied successfully."
    And the grid should contain 2 elements
    When I click on the "Reject all" action of the row which contains "jacket"
    And I press the "Send" button in the popin
    Then I should see the flash message "The proposal has been refused."
    And the grid should contain 1 element

  Scenario: Successfully display only proposals that the current user can approve
    Given I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 2 elements
    And I should see entities tshirt and sweater

  Scenario: Successfully review a proposal with a new simple select value
    Given I am logged in as "Julia"
    And I am on the "additional_materials" attribute page
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code   | en_US  | fr_FR  | de_DE  |
      | Blue   | Blue   | Bleu   | Blau   |
    And I am on the "weather_conditions" attribute page
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code   | en_US   | fr_FR  | de_DE  |
      | Blue   | Blue    | Bleu   | Blau   |
    And I logout
    And Mary proposed the following change to "tshirt":
      | tab        | field               | value|
      | Additional | Weather conditions  | Blue |
    And I am logged in as "Julia"
    When I am on the proposals page
    Then I should see the following proposals:
      | product | author | attribute          | original | new  |
      | tshirt  | Mary   | weather_conditions | Dry      | Blue |
