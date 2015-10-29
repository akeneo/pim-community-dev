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
      | tshirts          | Redactor   | edit   |
    And the following products:
      | sku    | family | categories      |
      | tshirt | pants  | 2014_collection |
      | jacket | pants  | tshirts         |

  Scenario: I can partially approve from the proposal grid
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Peter"
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
      | type    | message                                                                       |
      | success | Peter Williams has accepted the value for Name for the product: tshirt        |
      | success | Peter Williams has accepted the value for Description for the product: tshirt |
    When I click on the notification "Peter Williams has accepted the value for Name for the product: tshirt"
    Then I should be on the product "tshirt" edit page
    And the product "tshirt" should have the following values:
      | name-en_US               | Summer t-shirt             |
      | description-en_US-mobile | Summer t-shirt description |

  Scenario: I can partially approve from the proposal grid with a comment
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Peter"
    And I am on the proposals page
    And I partially approve:
      | product | author | attribute   | locale | scope  | comment                                      |
      | tshirt  | Mary   | description | en_US  | mobile | Yes, remember to update the price next time! |
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                       | comment                                      |
      | success | Peter Williams has accepted the value for Description for the product: tshirt | Yes, remember to update the price next time! |
    When I click on the notification "Peter Williams has accepted the value for Description for the product: tshirt"
    Then I should be on the product "tshirt" edit page
    And the product "tshirt" should have the following values:
      | description-en_US-mobile | Summer t-shirt description |

  Scenario: I can partially approve from the product draft page
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Peter"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I partially approve:
      | product | author | attribute   | locale | scope  |
      | tshirt  | Mary   | name        | en_US  |        |
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                |
      | success | Peter Williams has accepted the value for Name for the product: tshirt |
    When I click on the notification "Peter Williams has accepted the value for Name for the product: tshirt"
    Then I should be on the product "tshirt" edit page
    And the product "tshirt" should have the following values:
      | name-en_US | Summer t-shirt |

  Scenario: I can partially approve proposal that have only one change
    Given Mary proposed the following change to "jacket":
      | field       | value         |
      | Name        | Summer jacket |
    And I am logged in as "Peter"
    And I edit the "jacket" product
    And I visit the "Proposals" tab
    Then I should get the following proposals:
      | jacket | Mary |
    And I partially approve:
      | product | author | attribute   | locale | scope  |
      | jacket  | Mary   | name        | en_US  |        |
    Then I should not get the following proposals:
      | jacket | Mary |

  Scenario: I can partially approve proposal and keep the original proposal
    Given Mary proposed the following change to "tshirt":
      | field       | value                      |
      | Name        | Summer t-shirt             |
      | Description | Summer t-shirt description |
    And I am logged in as "Peter"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I partially approve:
      | product | author | attribute   | locale | scope  |
      | tshirt  | Mary   | name        | en_US  |        |
    Then I should not see the following partial approve button:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |
    But I should see the following partial approve button:
      | product | author | attribute   | locale | scope  |
      | tshirt  | Mary   | description | en_US  | mobile |

  Scenario: I dont see the propposal tab if I can't approve anything
    Given Mary proposed the following change to "jacket":
      | field       | value         |
      | Name        | Summer jacket |
    And I am logged in as "Julia"
    And I edit the "jacket" product
    Then I should not see the "Proposals" tab

  Scenario: I can partially approve only attribute that I can edit
    Given Mary proposed the following change to "tshirt":
      | field       | value         | tab                 |
      | Name        | Summer jacket | Product information |
      | Price       | 10 USD        | Marketing           |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | edit   |
      | marketing       | Manager    | view   |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    Then I should not see the following partial approve button:
      | product | author | attribute |
      | tshirt  | Mary   | price     |
    But I should see the following partial approve button:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |

  Scenario: I can partially approve only on locale I can edit
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I change the Description to "Body whool"
    And I switch the locale to "fr_FR"
    And I change the Description to "Maillot de corps"
    And I save the product
    And I press the "Send for approval" button
    And I press the "Send" button in the popin
    And I logout
    When I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I partially approve:
      | product | author | attribute   | locale | scope  |
      | tshirt  | Mary   | description | fr_FR  | mobile |
    Then the product "tshirt" should have the following values:
      | description-en_US-mobile |                  |
      | description-fr_FR-mobile | Maillot de corps |

  Scenario: I should not be able to partially accept a value on a draft in progress
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I change the Name to "The only one"
    And I save the product
    And I logout
    When I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    Then I should not see the following partial approve button:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |
    And I should not see the "Approve changes on attribute name of the product tshirt" button

  Scenario: I should not be able to see changes on attributes I am not able to see
    Given Mary proposed the following change to "tshirt":
      | field       | value         | tab                 |
      | Name        | Summer jacket | Product information |
      | Price       | 10 USD        | Marketing           |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | info            | Manager    | view   |
      | marketing       | Manager    | none   |
    And I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" tab
    And I should not see the following changes on the proposals:
      | product | author | attribute |
      | tshirt  | Mary   | price     |
    And I should see the following changes on the proposals:
      | product | author | attribute | locale |
      | tshirt  | Mary   | name      | en_US  |