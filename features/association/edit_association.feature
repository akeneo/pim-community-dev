@javascript
Feature: Edit an association
  In order to manage existing associations in the catalog
  As a user
  I need to be able to edit an association

  Background:
    Given the following associations:
      | code       |
      | up_sell    |
      | cross_sell |
    And I am logged in as "admin"

  Scenario: Successfully display the edit view for an association
    Given I am on the "up_sell" association page
    Then I should see the Code field
    And the field Code should be disabled

  Scenario: Successfully edit an association
    Given I am on the "up_sell" association page
    When I fill in the following information:
      | English (United States) | My upsell |
    And I press the "Save" button
    Then I should see "My upsell"

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "up_sell" association page
    When I fill in the following information:
      | English (United States) | up_sell |
    Then I should see "There are unsaved changes."

  Scenario: Successfully have a confirmation popup when I change page with unsaved changes
    Given I am on the "up_sell" association page
    When I fill in the following information:
      | English (United States) | up_sell |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                        |
      | content | You will lose changes to the association if you leave this page. |
