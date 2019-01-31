@javascript
Feature: Manage notifications preferences
  In order to keep my productivity and don't be spammed by notifications
  As a regular user
  I need to be able to manage notifications I receive about product draft proposals

  Background:
    Given an "clothing" catalog configuration
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
      | 2014_collection  | IT support | view   |
    And the following products:
      | sku    | family  | categories      |
      | tshirt | jackets | 2014_collection |

  Scenario: I can only edit notifications preferences that are relevant to me
    Given I am logged in as "Julia"
    And I edit the "Julia" user
    And I visit the "Notifications" tab
    Then I should see the text "When new proposal to review"
    And I should see the text "When proposal is accepted or rejected"
    And I logout
    Given I am logged in as "Mary"
    And I edit the "Mary" user
    And I visit the "Notifications" tab
    Then I should not see the text "When new proposal to review"
    And I should see the text "When proposal is accepted or rejected"
    And I logout
    Given I am logged in as "Peter"
    And I edit the "Peter" user
    And I visit the "Notifications" tab
    Then I should see the text "When new proposal to review"
    And I should not see the text "When proposal is accepted or rejected"

  Scenario: I can disable notification I receive when there is a new proposal on product I own
    Given I am logged in as "Julia"
    And I edit the "Julia" user
    When I visit the "Notifications" tab
    And I uncheck the "When new proposal to review" switch
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I logout
    And Mary proposed the following change to "tshirt":
      | field | value          |
      | Name  | Summer t-shirt |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 0 new notification

  Scenario: I can disable notification I receive when someone updated my proposal
    Given I am logged in as "Mary"
    When I edit the "Mary" user
    When I visit the "Notifications" tab
    Then I should not see the text "When new proposal to review"
    But I should see the text "When proposal is accepted or rejected"
    When I uncheck the "When proposal is accepted or rejected" switch
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I am on the "tshirt" product page
    And I change the Name to "Star wars shirt (R2D2 version)"
    And I save the product
    And I press the "Send for approval" button
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Approve all" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 0 new notification
