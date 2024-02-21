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
