@javascript
Feature: Approve notifications
  In order to easily quickly now if my proposals have been reviewed
  As a proposal redactor
  I need to be able to see a notification when the owner approve a proposal

  Background:
    Given an "clothing" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
    And the following products:
      | sku    | family  | categories      |
      | tshirt | jackets | 2014_collection |
    And Mary proposed the following change to "tshirt":
      | field | value          |
      | Name  | Summer t-shirt |

  @purge-messenger
  Scenario: A notification is sent when I approve a proposal from the proposal grid
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Approve all" action of the row which contains "Summer t-shirt"
    And I fill in this comment in the popin: "You did a nice job on this proposal. Thank you!"
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                               | comment                                         |
      | success | Julia Stark has accepted your proposal for the product Summer t-shirt | You did a nice job on this proposal. Thank you! |
    When I click on the notification "Julia Stark has accepted your proposal for the product Summer t-shirt"
    Then I should be on the product "tshirt" edit page
    And 1 event of type "product.updated" should have been raised

  Scenario: A notification is sent when I approve a proposal from the product draft page
    Given I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" column tab
    And I click on the "Approve all" action of the row which contains "Summer t-shirt"
    And I fill in this comment in the popin: "You did a nice job on this proposal. Thank you!"
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                               | comment                                         |
      | success | Julia Stark has accepted your proposal for the product Summer t-shirt | You did a nice job on this proposal. Thank you! |
    When I click on the notification "Julia Stark has accepted your proposal for the product Summer t-shirt"
    Then I should be on the product "tshirt" edit page

  @purge-messenger
  Scenario: A notification is sent when I approve a proposal from mass approval
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I select rows tshirt
    And I press the "Approve all selected" button
    And I fill in this comment in the popin: "You did a nice job on this proposal. Thank you!"
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                               | comment                                         |
      | success | Julia Stark has accepted your proposal for the product Summer t-shirt | You did a nice job on this proposal. Thank you! |
    When I click on the notification "Julia Stark has accepted your proposal for the product Summer t-shirt"
    Then I should be on the product "tshirt" edit page
    And 1 event of type "product.updated" should have been raised
