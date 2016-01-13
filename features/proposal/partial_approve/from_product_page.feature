@javascript
Feature: Partial approve
  In order to easily accept changes in proposals
  As a product owner
  I need to be able to partialy approve a proposal

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

  Scenario: I can partially approve from the product draft page
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I partially approve:
      | product | author | attribute | locale | scope |
      | tshirt  | Mary   | name      | en_US  |       |
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                              |
      | success | Julia Stark has accepted the value for Name for the product: tshirt |
    When I click on the notification "Julia Stark has accepted the value for Name for the product: tshirt"
    Then I should be on the product "tshirt" edit page
    And the product "tshirt" should have the following values:
      | name-en_US | Summer t-shirt |
