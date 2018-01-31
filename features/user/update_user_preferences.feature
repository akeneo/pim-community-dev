@javascript
Feature: Update user preferences
  In order for users to be able to choose their preferences
  As an administrator
  I need to synchronize user preferences with the catalog configuration

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Peter"

  @skip
  Scenario: Successfully delete a tree used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Default tree"
    And I should see the text "2013 collection"
    When I am on the "tablet" channel page
    Then I should see the Code, English (United States), Currencies, Locales and Category tree fields
    And I fill in the following information:
      | Category tree | 2014 collection |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And I edit the "2013_collection" category
    And I press the "Delete" button and wait for modal
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Default tree (required) 2014 collection"

  Scenario: Successfully delete a channel used by a user
    Given I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog scope"
    And I should see the text "Print"
    When I am on the "print" channel page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog scope (required) Ecommerce"

  Scenario: Successfully disable a locale used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I change the "Catalog locale" to "fr_FR"
    And I save the user
    Then I should see the flash message "User saved"
    And I should not see the text "There are unsaved changes."
    When I visit the "Additional" tab
    Then I should see the text "Catalog locale"
    And I should see the text "fr_FR"
    When I am on the "ecommerce" channel page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog locale (required) de_DE"
