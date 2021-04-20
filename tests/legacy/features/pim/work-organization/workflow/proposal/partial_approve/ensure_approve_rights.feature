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

  Scenario: I don't see the proposal tab if I can't approve anything
    Given Mary proposed the following change to "jacket":
      | field | value         |
      | Name  | Summer jacket |
    And I am logged in as "Mary"
    When I edit the "jacket" product
    Then I should not see the "Proposals" column tab

  Scenario: I am informed if a proposal can be only partially reviewed
    Given Mary proposed the following change to "tshirt":
      | field | value         | tab                 |
      | Name  | Summer jacket | Product information |
      | Price | 10 USD        | Marketing           |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | edit   |
      | marketing       | Manager    | view   |
    And I am logged in as "Julia"
    When I edit the "tshirt" product
    And I visit the "Proposals" column tab
    Then the row "Mary" should contain the texts:
      | column      | value                     |
      | Proposed at | can be partially reviewed |
    When I am on the proposals page
    Then the row "Mary" should contain the texts:
      | column      | value                     |
      | Proposed at | can be partially reviewed |

  Scenario: I can partially approve only attribute that I can edit
    Given Mary proposed the following change to "tshirt":
      | field | value         | tab                 |
      | Name  | Summer jacket | Product information |
      | Price | 10 USD        | Marketing           |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | edit   |
      | marketing       | Manager    | view   |
    And I am logged in as "Julia"
    When I edit the "tshirt" product
    And I visit the "Proposals" column tab
    Then I should not see the following partial approve button:
      | product | author | attribute |
      | tshirt  | Mary   | price     |
    But I should see the following partial approve button:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |

  Scenario: I can partially approve only on locale I can edit
    Given the following locale accesses:
      | locale | user group | access |
      | fr_FR  | Redactor   | edit   |
      | fr_FR  | Manager    | view   |
    And Mary proposed the following change to "tshirt":
      | field       | locale | value            |
      | Description | en_US  | Body whool       |
      | Description | fr_FR  | Maillot de corps |
    And I am logged in as "Julia"
    When I edit the "tshirt" product
    And I visit the "Proposals" column tab
    Then I should see the following partial approve button:
      | product | author | attribute   | locale |
      | tshirt  | Mary   | description | en_US  |
    But I should not see the following partial approve button:
      | product | author | attribute   | locale |
      | tshirt  | Mary   | description | fr_FR  |

  Scenario: I should not be able to partially accept a value on a draft in progress
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I change the Name to "The only one"
    And I save the product
    And I logout
    And I am logged in as "Julia"
    When I edit the "tshirt" product
    And I visit the "Proposals" column tab
    Then I should not see the following partial approve button:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |
