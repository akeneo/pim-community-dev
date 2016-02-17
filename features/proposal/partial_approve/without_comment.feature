@javascript
Feature: Partial approve
  In order to easily accept changes in proposals
  As a product owner
  I need to be able to partially approve a proposal

  Background:
    Given an "clothing" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
      | tshirts          | Redactor   | edit   |
      | tshirts          | Manager    | own    |
    And the following products:
      | sku    | family | categories      |
      | tshirt | pants  | 2014_collection |
      | jacket | pants  | tshirts         |

  Scenario: I can partially approve from the proposal grid
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Julia"
    And I am on the proposals page
    And I partially approve:
      | product | author | attribute   | locale | scope  |
      | tshirt  | Mary   | name        | en_US  |        |
      | tshirt  | Mary   | description | en_US  | mobile |
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 2 new notification
    And I should see notification:
      | type    | message                                                                          |
      | success | Julia Stark has accepted value(s) for Name for the product Summer t-shirt        |
      | success | Julia Stark has accepted value(s) for Description for the product Summer t-shirt |
    When I click on the notification "Julia Stark has accepted value(s) for Name for the product Summer t-shirt"
    Then I should be on the product "tshirt" edit page
    And the product "tshirt" should have the following values:
      | name-en_US               | Summer t-shirt             |
      | description-en_US-mobile | Summer t-shirt description |
