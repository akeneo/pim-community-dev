@javascript
Feature: Edit a product group
  In order to manage existing product groups for the catalog
  As a user
  I need to be able to edit a product group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the "similar_boots" product group page
    And I visit the "Properties" tab

  Scenario: Successfully edit a group
    Then I should see the Code and Type fields
    And the fields Code and Type should be disabled
    And I should not see the Axis field
    When I fill in the following information:
      | English (United States) | My similar boots |
    And I press the "Save" button
    Then I should see "My similar boots"

  Scenario: Successfully display a message when there are unsaved changes
    Given I fill in the following information:
      | English (United States) | My similar boots |
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                          |
      | content | You will lose changes to the product group if you leave this page. |
