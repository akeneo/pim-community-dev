@javascript
Feature: Change system locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of the pim without changing user locales

  Scenario: Successfully change pim locale without changing current user locales
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"
    Then I edit the system configuration
    And I uncheck the "Default" switch
    And I select French language
    And I save the configuration
    Then the user "Peter" should have "en" locale
    And the user "Julia" should have "en" locale
