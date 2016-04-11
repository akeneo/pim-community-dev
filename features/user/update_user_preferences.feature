@javascript
Feature: Update user preferences
  In order for users to be able to choose their preferences
  As an administrator
  I need to synchronize user preferences with the catalog configuration

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a tree used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Default tree"
    And I should see the text "2013 collection"
    When I edit the "tablet" channel
    And I change the "Category tree" to "2014 collection"
    And I save the channel
    And I should see the flash message "Channel successfully saved"
    And I edit the "2013_collection" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Default tree"
    And I should see the text "2014 collection"
    And I should not see "2013 collection"

  Scenario: Successfully delete a channel used by a user
    Given I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog scope"
    And I should see the text "Print"
    When I edit the "Print" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog scope"
    And I should see the text "Ecommerce"
    And I should not see "Print"

  Scenario: Successfully disable a locale used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I change the "Catalog locale" to "fr_FR"
    And I save the user
    And I should see the flash message "User saved"
    When I visit the "Additional Information" tab
    Then I should see "Catalog locale"
    And I should see "fr_FR"
    When I edit the "ecommerce" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see the text "Catalog locale"
    And I should see the text "de_DE"
    And I should not see "fr_FR"
