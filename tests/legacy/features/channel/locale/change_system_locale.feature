@javascript
Feature: Change system locale
  In order to change locale
  As an administrator
  I need to be able to change the locale of the pim without changing user locales

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change pim locale without changing current user locales
    Given I am on the System index page
    And I select French locale
    And I save the configuration
    Then the user "Peter" should have "en" locale
    And the user "Julia" should have "en" locale

  Scenario: Successfully display a localized login form according to the system locale
    Given I am on the System index page
    And I select French locale
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I logout
    And I should see the "Connexion" button
