@javascript
Feature: Change user locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of any user without reloading browser

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change my locale
    Given I edit my profile
    Then I visit the "Interfaces" tab
    And I fill in the following information:
     | Ui locale | fr_FR |
    And I save the user
    Then I should see "Collecter"

  Scenario: Successfully change Mary's locale
    Given I edit the "mary" user
    Then I visit the "Interfaces" tab
    And I fill in the following information:
     | Ui locale | fr_FR |
    And I save the user
    Then I should see "Collect"

  Scenario: Should only see translated locales
    Given I edit my profile
    And I visit the "Interfaces" tab
    Then I should see English locale option
    And I should not see Breton locale option
