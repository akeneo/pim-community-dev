@javascript
Feature: Edit a category
  In order to be able to modify the category tree
  As a product manager
  I need to be able to edit a category

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit a category
    Given I edit the "Sandals" category
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My sandals |
    And I save the category
    Then I should see the flash message "Category successfully updated"
    And I should be on the category "sandals" edit page
    And I should see the text "My sandals"

  Scenario: Go to category edit page from the category tree
    Given I am on the categories page
    And I select the "2014 collection" tree
    And I click on the "summer_collection" category
    Then I should be on the category "summer_collection" edit page

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I edit the "winter_boots" category
    When I fill in the following information:
      | English (United States) | My winter boots |
    And I click on the Akeneo logo
    Then I should see "You will lose changes to the category if you leave the page." in popup

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I edit the "winter_boots" category
    When I fill in the following information:
      | English (United States) | My winter boots |
    Then I should see the text "There are unsaved changes."
