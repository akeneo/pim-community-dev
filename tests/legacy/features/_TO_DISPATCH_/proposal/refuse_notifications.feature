@javascript
Feature: Refuse notifications
  In order to easily quickly now if my proposals have been reviewed
  As a proposal redactor
  I need to be able to see a notification when the owner reject a proposal

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

  Scenario: A notification is sent when I approve a proposal from the proposal grid
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Reject all" action of the row which contains "Summer t-shirt"
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       |
      | error | Julia Stark has rejected your proposal for the product tshirt |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the proposal grid
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Reject all" action of the row which contains "Summer t-shirt"
    And I fill in this comment in the popin: "To be reviewed, this value looks wrong."
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       | comment                                 |
      | error | Julia Stark has rejected your proposal for the product tshirt | To be reviewed, this value looks wrong. |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the product draft page
    Given I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" column tab
    And I click on the "Reject all" action of the row which contains "Summer t-shirt"
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       |
      | error | Julia Stark has rejected your proposal for the product tshirt |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from the product draft page
    Given I am logged in as "Julia"
    And I edit the "tshirt" product
    And I visit the "Proposals" column tab
    And I click on the "Reject all" action of the row which contains "Summer t-shirt"
    And I fill in this comment in the popin: "To be reviewed, this value looks wrong."
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       | comment                                 |
      | error | Julia Stark has rejected your proposal for the product tshirt | To be reviewed, this value looks wrong. |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from mass approval
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I select row tshirt
    And I press the "Reject all selected" button
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       |
      | error | Julia Stark has rejected your proposal for the product tshirt |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page

  Scenario: A notification is sent when I approve a proposal from mass approval
    Given I am logged in as "Julia"
    And I am on the proposals page
    And I select row tshirt
    And I press the "Reject all selected" button
    And I fill in this comment in the popin: "To be reviewed, this value looks wrong."
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                       | comment                                 |
      | error | Julia Stark has rejected your proposal for the product tshirt | To be reviewed, this value looks wrong. |
    When I click on the notification "Julia Stark has rejected your proposal for the product tshirt"
    And I wait to be on the "tshirt" product page
    Then I should be on the product "tshirt" edit page
