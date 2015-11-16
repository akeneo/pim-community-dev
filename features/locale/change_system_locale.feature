@javascript
Feature: Change system locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of the pim without changing user locales

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change pim locale without changing current user locales
    Given I edit the system configuration
    And I select French locale
    And I save the configuration
    Then the user "Peter" should have "en" locale
    And the user "Julia" should have "en" locale

  Scenario: Should only see translated locales
    Given I edit the system configuration
    Then I should see English locale option
    And I should not see Breton locale option
