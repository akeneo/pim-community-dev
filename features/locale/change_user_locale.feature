@javascript
Feature: Change user locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of any user without reloading browser

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  @jira https://akeneo.atlassian.net/browse/PIM-4937
  Scenario: Successfully change my locale
    Given I edit my profile
    Then I visit the "Interfaces" tab
    And I fill in the following information:
     | Ui locale  | fr_FR              |
    And I save the user
    Then I should see "Collecter"

  @jira https://akeneo.atlassian.net/browse/PIM-4937
  Scenario: Successfully change Mary's locale
    Given I edit the "mary" user
    Then I visit the "Interfaces" tab
    And I fill in the following information:
     | Ui locale  | fr_FR              |
    And I save the user
    Then I should see "Collect"
