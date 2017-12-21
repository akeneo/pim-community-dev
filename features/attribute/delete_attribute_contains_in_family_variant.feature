@javascript
Feature: Delete an attribute
  Contains in a family variant
  As a product manager
  I need to delete a text attribute

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-7062
  Scenario: Successfully delete a text attribute used in a family variant
    Given I am on the "Clothing" family page
    And I visit the "Variants" tab
    And I click on the "Clothing by material and size" row
    And I should see the text "composition"
    And I press the cancel button in the popin
    When I am on the "composition" attribute page
    And I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Attribute successfully deleted"
