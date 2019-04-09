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
      | tshirt  | tshirts  | 2014_collection | dry                |
      | sweater | sweaters | 2014_collection |                    |
      | jacket  | jackets  | 2015_collection |                    |
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

  Scenario: Successfully sort proposals in the grid
    Given I am logged in as "Peter"
    When I am on the proposals page
    Then the grid should contain 1 elements
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    When I am on the proposals page
    And I reload the page
    Then the grid should contain 3 elements
    And the rows should be sorted descending by proposed at
    And I collapse the column
    And I should be able to sort the rows by author and proposed at

  Scenario Outline: Successfully filter proposals in the grid
    Given I am logged in as "Peter"
    When I am on the proposals page
    Then the grid should contain 1 elements
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    When I am on the proposals page
    And I reload the page
    Then the grid should contain 3 elements
    And I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    And I should see entities <result>

    Examples:
      | filter              | operator     | value         | count | result                  |
      | author              | in list      | Julia         | 1     | jacket                  |
      | author              | in list      | Sandra,Mary   | 2     | sweater, tshirt         |
      | label_or_identifier | equals       | tshirt        | 1     | tshirt                  |
      | identifier          | in list      | tshirt,jacket | 2     | tshirt, jacket          |
      | name                | is not empty |               | 3     | tshirt, sweater, jacket |
      | description         | is not empty |               | 1     | tshirt                  |
      | price               | is not empty | USD           | 1     | jacket                  |

  Scenario: Successfully apply multiple filters on proposal grid
    Given I am logged in as "Peter"
    When I am on the proposals page
    Then the grid should contain 1 elements
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    When I am on the proposals page
    And I reload the page
    Then the grid should contain 3 elements
    And I show the filter "name"
    And I filter by "name" with operator "is not empty" and value ""
    And I show the filter "price"
    And I filter by "price" with operator "is not empty" and value "USD"
    Then the grid should contain 1 element
    And I should see entities "jacket"

  Scenario: Successfully approve or reject a proposal
    Given I am logged in as "Peter"
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | own    |
    And I am on the proposals page
    And I reload the page
    And I should see entities tshirt, sweater and jacket
    When I click on the "Approve all" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    Then I should see the flash message "The proposal has been applied successfully."
    And the grid should contain 2 elements
    When I click on the "Reject all" action of the row which contains "jacket"
    And I press the "Send" button in the popin
    Then I should see the flash message "The proposal has been refused."
    And I reload the page
    And the grid should contain 1 element

  Scenario: Successfully display only proposals that the current user can approve
    Given I am logged in as "Julia"
    And I am on the proposals page
    Then the grid should contain 2 elements
    And I should see entities tshirt and sweater

  Scenario: Successfully review a proposal with a new simple select value
    Given I am logged in as "Julia"
    And I am on the "tshirts" family page
    And I visit the "Attributes" tab
    And I add available attribute Weather conditions
    And I save the family
    And I am on the "additional_materials" attribute page
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code | en_US | fr_FR | de_DE |
      | Blue | Blue  | Bleu  | Blau  |
    And I am on the "weather_conditions" attribute page
    And I visit the "Options" tab
    And I create the following attribute options:
      | Code | en_US | fr_FR | de_DE |
      | Blue | Blue  | Bleu  | Blau  |
    And I logout
    And Mary proposed the following change to "tshirt":
      | tab                    | field              | value |
      | Additional information | Weather conditions | Blue  |
    And I am logged in as "Julia"
    When I am on the proposals page
    Then I should see the following proposals:
      | product | author | attribute          | original | new  |
      | tshirt  | Mary   | weather_conditions |          | Blue |

  @jira https://akeneo.atlassian.net/browse/PIM-5825
  Scenario: Successfully display a proposal even when an attribute has been deleted
    Given I am logged in as "Julia"
    And I am on the "name" attribute page
    And I press the secondary action "Delete"
    And I confirm the removal
    When I am on the proposals page
    Then I should see the following proposals:
      | product | author | attribute   | original | new                        |
      | tshirt  | Mary   | description |          | Summer t-shirt description |
    And I click on the "Reject All" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    And I refresh current page
    And I should see the text "There is no proposal to review"
    When I logout
    And I am logged in as "Mary"
    And I am on the "tshirt" product page
    Then I should not see the Name field

  @jira https://akeneo.atlassian.net/browse/PIM-5825
  Scenario: Does not display a proposal when all of its attributes have been deleted
    Given I am logged in as "Julia"
    And I am on the "name" attribute page
    And I press the secondary action "Delete"
    And I confirm the removal
    And I am on the "description" attribute page
    And I press the secondary action "Delete"
    And I confirm the removal
    When I am on the proposals page
    Then I should see the text "There is no proposal to review"
