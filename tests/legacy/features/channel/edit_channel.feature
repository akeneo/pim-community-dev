@javascript
Feature: Edit a channel
  In order to manage existing channels in the catalog
  As an administrator
  I need to be able to edit a channel

  Background:
    Given a "footwear" catalog configuration

  @critical
  Scenario: Successfully edit a channel to enable a locale and disable unused locales when deleting a channel
    Given I am logged in as "Peter"
    When  I am on the "tablet" channel page
    And I change the "Locales" to "Breton (France)"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I am on the locales page
    And I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 3 elements
    And I should see locales "en_US", "br_FR" and "fr_FR"
    When I am on the "tablet" channel page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I am on the locales page
    Then the grid should contain 2 element
    And I should see locale "en_US" and "fr_FR"

  @jira https://akeneo.atlassian.net/browse/PIM-6025
  Scenario: Successfully replace a channel locale by another one when there is only one channel
    Given I am logged in as "Peter"
    And I am on the channels page
    And I click on the "Delete" action of the row which contains "Tablet"
    And I confirm the deletion
    And I am on the "mobile" channel page
    When I change the "Locales" to "German (Germany)"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the locales page
    And I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 1 elements
    And I should see locales "de_DE"

  Scenario: Successfully updates a channel conversion units
    Given I am logged in as "Peter"
    And I am on the "tablet" channel page
    And I fill in the following information:
      | Volume | Liter      |
      | Length | Millimeter |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the field Volume should contain "Liter"
    And the field Length should contain "Millimeter"
    And the field Weight should contain "Do not convert"
    When I fill in the following information:
      | Volume | Do not convert |
      | Length | Millimeter     |
      | Weight | Gram           |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the field Weight should contain "Gram"
    And the field Length should contain "Millimeter"
    And the field Volume should contain "Do not convert"
