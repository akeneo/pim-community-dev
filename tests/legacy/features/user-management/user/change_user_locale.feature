@javascript
Feature: Change user locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of any user without reloading browser

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change Mary's locale
    Given I edit the "mary" user
    Then I visit the "Interfaces" tab
    And I fill in the following information:
     | UI locale | French (France) |
    And I save the user
    Then I should see the flash message "User saved"
    Then I should not see the text "Collecter"
