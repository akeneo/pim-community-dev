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

  Scenario: I can partially approve proposal and keep the original proposal
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" column tab
    And I partially approve:
      | product | author | attribute | locale | scope |
      | tshirt  | Mary   | name      | en_US  |       |
    Then I should not see the following partial approve button:
      | product         | author | attribute | locale |
      | Summer t-shirt  | Mary   | name      | en_US  |
    But I should see the following partial approve button:
      | product         | author | attribute   | locale | scope  |
      | Summer t-shirt  | Mary   | description | en_US  | mobile |
