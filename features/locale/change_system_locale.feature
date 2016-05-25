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

  Scenario: Successfully display a localized login form according to the system locale
    Given I edit the system configuration
    And I select de_DE locale
    And I save the configuration
    Then I should see the text "Successfully updated"
    Then I logout
    Then I should see the "Anmelden" button
    When I am logged in as "Julia"
    And I edit the system configuration
    And I select fr_FR locale
    And I save the configuration
    Then I should see the text "Successfully updated"
    Then I logout
    Then I should see the "Connexion" button
