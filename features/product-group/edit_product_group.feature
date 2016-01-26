@javascript
Feature: Edit a product group
  In order to manage existing product groups for the catalog
  As a product manager
  I need to be able to edit a product group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
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

  Scenario: Successfully browse to the history tab after save
    When I fill in the following information:
      | English (United States) | My similar boots |
    And I press the "Save" button
    Then I visit the "History" tab
    Then I should see the text "label-en_US: Boots"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I fill in the following information:
      | English (United States) | My similar boots |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                          |
      | content | You will lose changes to the product group if you leave this page. |

  Scenario: Successfully display a message when there are unsaved changes
    Given I fill in the following information:
      | English (United States) | My similar boots |
    Then I should see the text "There are unsaved changes."

  Scenario: Successfully retrieve the last visited tab
    Given I am on the categories page
    And I am on the "similar_boots" product group page
    And I should see "Code"
    And I should see "Type"

  Scenario: Successfully retrieve the last visited tab after a save
    Given I save the family
    And I should see "Code"
    And I should see "Type"
