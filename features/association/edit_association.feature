@javascript
Feature: Edit an association
  In order to manage existing associations in the catalog
  As a user
  I need to be able to edit an association

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the edit view for an association
    Given I am on the "UPSELL" association page
    Then I should see the Code field
    And the field Code should be disabled

  Scenario: Successfully edit an association
    Given I am on the "SUBSTITUTION" association page
    When I fill in the following information:
      | English (United States) | My substitution |
    And I press the "Save" button
    Then I should see "My substitution"

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "PACK" association page
    When I fill in the following information:
      | English (United States) | My pack |
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                        |
      | content | You will lose changes to the association if you leave this page. |
