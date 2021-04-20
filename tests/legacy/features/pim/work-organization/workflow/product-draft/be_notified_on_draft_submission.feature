@javascript
Feature: Be notified on draft submission
  In order to control which data should be applied to a product
  As a product manager
  I need to be notified when someone send a proposal on one of my products

  Background:
    Given a "clothing" catalog configuration
    And the following products:
    | sku          | family  | categories | name-en_US | description-en_US-mobile | price  | handmade | length        |
    | my-jacket    | jackets | winter_top | Jacket     | An awesome jacket        | 45 USD | 0        | 60 CENTIMETER |
    | my-jacket-hm | jackets | winter_top | Jacket HM  | An awesome jacket        | 80 USD | 1        | 60 CENTIMETER |

  Scenario: Successfully be notified when someone sends a proposal for approval
    Given Mary proposed the following change to "my-jacket":
      | field | value         |
      | SKU   | jacket-not-hm |
    And Mary proposed the following change to "my-jacket-hm":
      | field | value     |
      | SKU   | jacket-hm |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 2 new notifications
    And I should see notification:
      | type | message                                                         |
      | add  | Mary Smith has sent a proposal to review for the product Jacket |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket HM"
    Then I should be on the proposals index page
    And the grid should contain 1 element
    And I should see the following proposal:
      | product   | author | attribute | original     | new       |
      | Jacket HM | Mary   | sku       | my-jacket-hm | jacket-hm |

  Scenario: Successfully be notified when someone sends a proposal for approval with a comment
    Given Mary proposed the following change to "my-jacket":
      | field | value         |
      | SKU   | jacket-not-hm |
    And Mary proposed the following change to "my-jacket-hm" with the comment "Please approve this fast.":
      | field | value     |
      | SKU   | jacket-hm |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 2 new notifications
    And I should see notification:
      | type | message                                                            | comment                   |
      | add  | Mary Smith has sent a proposal to review for the product Jacket HM | Please approve this fast. |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket HM"
    Then I should be on the proposals index page
    And the grid should contain 1 element
    And I should see the following proposal:
      | product   | author | attribute | original     | new       |
      | Jacket HM | Mary   | sku       | my-jacket-hm | jacket-hm |
