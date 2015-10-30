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
    And the following products:
      | sku     | family   | categories      |
      | tshirt  | jackets  | 2014_collection |

  Scenario: I can only edit notifications preferences that are relevant to me
    Given I am logged in as "Julia"
    And I am on my profile page
    And I press the "Edit" button
    And I visit the "Notifications" tab
    Then I should see "When new proposal to review"
    And I should see "When proposal is accepted or rejected"
    And I logout
    Given I am logged in as "Mary"
    And I am on my profile page
    And I press the "Edit" button
    And I visit the "Notifications" tab
    Then I should not see "When new proposal to review"
    And I should see "When proposal is accepted or rejected"
    And I logout
    Given I am logged in as "Peter"
    And I am on my profile page
    And I press the "Edit" button
    And I visit the "Notifications" tab
    Then I should see "When new proposal to review"
    And I should not see "When proposal is accepted or rejected"

  Scenario: I can disable notification I receive when there is a new proposal on product I own
    Given I am logged in as "Julia"
    And I am on my profile page
    And I press the "Edit" button
    And I visit the "Notifications" tab
    When I uncheck the "When new proposal to review" switch
    And I save the user
    And I logout
    And Mary proposed the following change to "tshirt":
      | field | value          |
      | Name  | Summer t-shirt |
    When I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 0 new notification

  Scenario: I can disable notification I receive when someone updated my proposal
    Given I am logged in as "Mary"
    And I am on my profile page
    And I press the "Edit" button
    And I visit the "Notifications" tab
    Then I should not see "When new proposal to review"
    But I should see "When proposal is accepted or rejected"
    When I uncheck the "When proposal is accepted or rejected" switch
    And I save the user
    And I am on the "tshirt" product page
    And I change the Name to "Star wars shirt (R2D2 version)"
    And I save the product
    And I press the "Send for approval" button
    And I press the "Send" button in the popin
    When I logout
    And I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Approve" action of the row which contains "tshirt"
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 0 new notification
