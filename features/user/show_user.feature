@javascript
Feature: Show a user
  In order to manage the users
  As an administrator
  I need to be able to show users information

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully show user locale
    Given I show the "Mary" user
    And I visit the "Interfaces" tab
    Then I should see "Ui locale"
    And I should see "en_US"

  Scenario: Successfully show my locale
    Given I am on the User profile show page
    And I visit the "Interfaces" tab
    Then I should see "Ui locale"
    And I should see "en_US"
