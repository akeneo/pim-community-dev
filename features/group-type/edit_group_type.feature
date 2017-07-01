@javascript
Feature: Edit a group type
  In order to manage existing group types in the catalog
  As an administrator
  I need to be able to edit a group type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a group type
    Given I am on the "X_SELL" group type page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My cross-sell type |
    And I press the "Save" button
    Then I should see the text "My cross-sell type"

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "VARIANT" group type page
    When I fill in the following information:
      | English (United States) | My variant |
    When I click on the Akeneo logo
    Then I should see "You will lose changes to the group type if you leave this page." in popup

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "VARIANT" group type page
    When I fill in the following information:
      | English (United States) | My variant |
    Then I should see the text "There are unsaved changes."
