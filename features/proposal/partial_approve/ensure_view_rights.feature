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

  Scenario: I should not be able to see changes on attributes I am not able to see
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I change the Description to "Body whool"
    And I switch the locale to "fr_FR"
    And I change the Description to "Maillot de corps"
    And I save the product
    Given Mary proposed the following change to "tshirt":
      | field | value  | tab       |
      | Price | 10 USD | Marketing |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | view   |
      | marketing       | Manager    | none   |
    And the following locale accesses:
      | locale | user group | access |
      | fr_FR  | Manager    | none   |
      | en_US  | Manager    | view   |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I should not see the following changes on the proposals:
      | product | author | attribute   | locale |
      | tshirt  | Mary   | price       |        |
      | tshirt  | Mary   | description | fr_FR  |
    And I should see the following changes on the proposals:
      | product | author | attribute   | locale |
      | tshirt  | Mary   | description | en_US  |
    And I should not see the following partial approve button:
      | product | author | attribute   | locale |
      | tshirt  | Mary   | description | fr_FR  |
      | tshirt  | Mary   | description | en_US  |
      | tshirt  | Mary   | price       |        |
