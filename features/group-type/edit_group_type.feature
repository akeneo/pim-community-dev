@javascript
Feature: Edit a group type
  In order to manage existing group types in the catalog
  As a user
  I need to be able to edit a group type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the edit view for a group type
    Given I am on the "X_SELL" group type page
    Then I should see the Code field
    And the field Code should be disabled

  Scenario: Successfully edit a group type
    Given I am on the "X_SELL" group type page
    When I fill in the following information:
      | English (United States) | My cross-sell type |
    And I press the "Save" button
    Then I should see "My cross-sell type"

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "VARIANT" group type page
    When I fill in the following information:
      | English (United States) | My variant |
    Then I should see "There are unsaved changes."
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                        |
      | content | You will lose changes to the association if you leave this page. |
