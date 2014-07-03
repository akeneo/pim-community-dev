@javascript
Feature: Edit a variant group
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to edit a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Properties" tab

  Scenario: Successfully edit a variant group
    Then I should see the Code and Axis fields
    And the fields Code and Axis should be disabled
    When I fill in the following information:
      | English (United States) | My boots |
    And I press the "Save" button
    Then I should see "My boots"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I fill in the following information:
      | English (United States) | My boots |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                          |
      | content | You will lose changes to the variant group if you leave this page. |

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I fill in the following information:
      | English (United States) | My boots |
    Then I should see "There are unsaved changes."
