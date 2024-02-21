@javascript
Feature: Login as a user into the application

  Scenario: Login as a user
    Given the "default" catalog configuration
    When I am logged in through the UI as "Mary"
    Then I am on the dashboard page
