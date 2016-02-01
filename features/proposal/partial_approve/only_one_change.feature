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

  Scenario: I can partially approve proposal that have only one change
    Given Mary proposed the following change to "jacket":
      | field | value         |
      | Name  | Summer jacket |
    And I am logged in as "Julia"
    And I edit the "jacket" product
    And I visit the "Proposals" tab
    And I should see the following proposals:
      | product | author | attribute | original | new           |
      | jacket  | Mary   | name      |          | Summer jacket |
    And I partially approve:
      | product | author | attribute | locale | scope |
      | jacket  | Mary   | name      | en_US  |       |
    Then I should not get the following proposals:
      | jacket | Mary |
