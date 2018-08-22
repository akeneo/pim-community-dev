@javascript
Feature: Edit an association type
  In order to manage existing association types in the catalog
  As a product manager
  I need to be able to edit an association type

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit an association type
    Given I am on the "SUBSTITUTION" association type page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My substitution |
    And I press the "Save" button
    Then I should see the text "My substitution"

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "PACK" association type page
    When I fill in the following information:
      | English (United States) | My pack |
    And I click on the Akeneo logo
    And I should see "You will lose changes to the association type if you leave this page." in popup

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "PACK" association type page
    When I fill in the following information:
      | English (United States) | My new substitution |
    Then I should see the text "There are unsaved changes."
